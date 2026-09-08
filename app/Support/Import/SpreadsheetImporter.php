<?php

declare(strict_types=1);

namespace App\Support\Import;

use App\Application\Api\Ap\ApApplicationService;
use App\Application\Api\Ar\ArApplicationService;
use App\Application\Api\Other\DomainApplicationService;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Ap\Supplier;
use App\Models\Ar\Customer;
use App\Models\Expense\ExpenseClaim;
use App\Models\Inventory\InventoryItem;
use App\Models\SpreadsheetImport;
use App\Services\Ap\SupplierService;
use App\Services\Ar\CustomerService;
use App\Services\Inventory\InventoryService;
use App\Support\Accounting\AccountingContext;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Import orchestration only: monetary calculations and document creation remain in domain services. */
final class SpreadsheetImporter
{
    public function authorize(string $kind, Company $company, AccountingBook $book): void
    {
        $context = app(AccountingContext::class);
        abort_unless(isset(ImportCatalog::schemas()[$kind]), 404);
        abort_unless($context->company()?->id === $company->id && $context->book()?->id === $book->id
            && (int) $book->company_id === $company->id && $context->can(ImportCatalog::permission($kind)), 403);
    }

    /**
     * @param  array{headers: list<string>, rows: list<array{row: int, cells: list<string>}>, formulas: bool}  $payload
     * @param  array<string, string>  $options
     * @return array<string, list<array{row: int, cells: list<string>}>>
     */
    public function validate(string $kind, Company $company, AccountingBook $book, array $payload, array $options): array
    {
        $this->authorize($kind, $company, $book);
        $headers = ImportCatalog::schemas()[$kind];
        if (array_slice($payload['headers'], 0, count($headers)) !== $headers) {
            $this->error(__('imports.headers_mismatch'));
        }
        $groups = [];
        $errors = [];
        foreach ($payload['rows'] as $row) {
            $cells = $row['cells'];
            // Ignore formatted template rows only when every cached value is blank/zero.
            if (! ImportCatalog::hasData($kind, $cells)) {
                continue;
            }
            try {
                $reference = $this->required($cells[0] ?? '', $kind === 'expenses' ? 46 : 50);
                if (ImportCatalog::reference($kind)) {
                    $this->required($cells[1] ?? '', 255);
                    foreach (array_slice($cells, 2, $kind === 'inventory-report' ? 6 : 4) as $value) {
                        $this->number($value, false);
                    }
                } elseif (in_array($kind, ['customers', 'suppliers', 'items'], true)) {
                    $this->required($cells[1] ?? '', 255);
                    $model = match ($kind) {
                        'customers' => Customer::class, 'suppliers' => Supplier::class, default => InventoryItem::class
                    };
                    if ($model::query()->where('company_id', $company->id)->where('code', $reference)->exists()) {
                        $this->error(__('imports.duplicate', ['reference' => $reference]));
                    }
                    if ($kind !== 'items' && mb_strlen($cells[2] ?? '') > 100) {
                        $this->error(__('imports.invalid_value'));
                    }
                } else {
                    $this->date($cells[1] ?? '');
                    if ($kind === 'expenses') {
                        $this->required($cells[2] ?? '', 255);
                        $this->number($cells[4] ?? '', true);
                        $this->required($options['employee_ref'] ?? '', 100);
                        if (ExpenseClaim::query()->where('company_id', $company->id)->where('number', 'XLS-'.$reference)->exists()) {
                            $this->error(__('imports.duplicate', ['reference' => $reference]));
                        }
                    } else {
                        $party = $this->party($kind, $company, $cells[2] ?? '');
                        if (strtoupper((string) $party->currency) !== strtoupper((string) $company->functional_currency)) {
                            $this->error(__('imports.foreign_currency'));
                        }
                        if (in_array($kind, ['sales', 'purchases'], true)) {
                            $this->required($cells[5] ?? '', 255);
                            $this->number($cells[6] ?? '', true);
                            $this->number($cells[7] ?? '', true);
                        } else {
                            $this->number($cells[5] ?? '', true);
                            $method = $this->method($cells[6] ?? '');
                            $this->account($company, $options[$method.'_account'] ?? '', true);
                        }
                    }
                }
                if (in_array($kind, ['sales', 'purchases', 'expenses'], true)) {
                    $this->account($company, $options['account'] ?? '');
                }
                if ($kind === 'items') {
                    $this->account($company, $options['inventory_account'] ?? '', inventory: true);
                    $this->account($company, $options['cogs_account'] ?? '');
                }
                $key = 'ref:'.$reference;
                if (isset($groups[$key])) {
                    if (! in_array($kind, ['sales', 'purchases'], true)) {
                        $this->error(__('imports.duplicate', ['reference' => $reference]));
                    }
                    $first = $groups[$key][0]['cells'];
                    if ($this->date($first[1]) !== $this->date($cells[1]) || $first[2] !== $cells[2]) {
                        $this->error(__('imports.header_conflict'));
                    }
                }
                if (! ImportCatalog::reference($kind) && DB::table('spreadsheet_import_keys')->where('company_id', $company->id)->where('kind', $kind)->where('source_key', hash('sha256', $reference))->exists()) {
                    $this->error(__('imports.duplicate', ['reference' => $reference]));
                }
                $groups[$key][] = $row;
            } catch (ValidationException $exception) {
                $errors[] = __('imports.row_error', ['row' => $row['row'], 'message' => $exception->validator->errors()->first()]);
            }
        }
        if ($errors !== []) {
            throw ValidationException::withMessages(['import' => array_slice($errors, 0, 50)]);
        }
        if ($groups === []) {
            $this->error(__('imports.empty'));
        }

        return $groups;
    }

    /** @return list<array{reference: string, url: string}> */
    public function commit(SpreadsheetImport $batch): array
    {
        return DB::transaction(function () use ($batch): array {
            $company = Company::query()->lockForUpdate()->findOrFail($batch->company_id);
            $book = AccountingBook::query()->findOrFail($batch->book_id);
            $this->authorize((string) $batch->kind, $company, $book);
            abort_unless((int) $batch->user_id === auth()->id(), 403);
            $batch = SpreadsheetImport::query()->lockForUpdate()->findOrFail($batch->id);
            if ($batch->completed_at !== null) {
                $this->error(__('imports.already_imported'));
            }
            /** @var array{headers: list<string>, rows: list<array{row: int, cells: list<string>}>, formulas: bool} $payload */
            $payload = $batch->payload;
            /** @var array<string, string> $options */
            $options = $batch->options;
            $kind = (string) $batch->kind;
            $groups = $this->validate($kind, $company, $book, $payload, $options);
            if (SpreadsheetImport::query()->where('company_id', $company->id)->where('book_id', $book->id)->where('kind', $kind)->where('fingerprint', $batch->fingerprint)->whereNotNull('completed_at')->exists()) {
                $this->error(__('imports.already_imported'));
            }
            $results = [];
            foreach ($groups as $rows) {
                $cells = $rows[0]['cells'];
                $reference = $cells[0];
                if (! ImportCatalog::reference($kind)) {
                    DB::table('spreadsheet_import_keys')->insert([
                        'company_id' => $company->id, 'import_id' => $batch->id, 'kind' => $kind, 'source_key' => hash('sha256', $reference),
                    ]);
                    $url = $this->create($kind, $company, $book, $rows, $options);
                    $results[] = ['reference' => $reference, 'url' => $url];
                }
            }
            $batch->update(['completed_at' => now(), 'results' => $results]);

            return $results;
        });
    }

    /** @param list<array{row: int, cells: list<string>}> $rows
     * @param  array<string, string>  $options
     */
    private function create(string $kind, Company $company, AccountingBook $book, array $rows, array $options): string
    {
        $c = $rows[0]['cells'];
        $ar = app(ArApplicationService::class);
        $ap = app(ApApplicationService::class);
        if ($kind === 'customers' || $kind === 'suppliers') {
            $data = ['code' => $c[0], 'name_ar' => $c[1], 'currency' => $company->functional_currency];
            if ($kind === 'customers') {
                $party = $ar->createCustomer($company, $data, auth()->user()?->id);
                if (($c[2] ?? '') !== '' && $c[2] !== '0') {
                    app(CustomerService::class)->addContact($party, ['name' => $c[1], 'phone' => $c[2]]);
                }

                return route('ar.customers.show', $party->id);
            }
            $party = $ap->createSupplier($company, $data, auth()->user()?->id);
            if (($c[2] ?? '') !== '' && $c[2] !== '0') {
                app(SupplierService::class)->addContact($party, ['name' => $c[1], 'phone' => $c[2]]);
            }

            return route('ap.suppliers');
        }
        if ($kind === 'items') {
            app(InventoryService::class)->defineItem($company, ['code' => $c[0], 'name' => $c[1], 'gl_account_code' => $options['inventory_account'], 'cogs_account_code' => $options['cogs_account']]);

            return route('imports.inventory');
        }
        $header = ['currency' => $company->functional_currency, 'exchange_rate' => '1', 'reference' => 'XLS:'.$c[0], 'created_by' => auth()->id()];
        if ($kind === 'expenses') {
            $claim = app(DomainApplicationService::class)->createExpenseDraft($company, $book, [
                'number' => 'XLS-'.$c[0], 'employee_ref' => $options['employee_ref'], 'claim_date' => $this->date($c[1]),
                'currency' => $company->functional_currency, 'description' => $c[2],
            ], [['expense_account' => $options['account'], 'description' => $c[2], 'amount' => $c[4]]]);

            return route('expenses.show', $claim->id);
        }
        $party = $this->party($kind, $company, $c[2]);
        if ($kind === 'sales' || $kind === 'purchases') {
            $lines = [];
            foreach ($rows as $row) {
                $line = $row['cells'];
                $lines[] = ['description' => $line[5], 'quantity' => $line[6], 'unit_price' => $line[7],
                    $kind === 'sales' ? 'revenue_account' : 'expense_account' => $options['account']];
            }
            $header['invoice_date'] = $this->date($c[1]);
            if ($party instanceof Customer) {
                $invoice = $ar->createInvoiceDraft($company, $book, $party, $lines, $header);

                return route('ar.invoices.show', $invoice->id);
            }
            $invoice = $ap->createInvoiceDraft($company, $book, $party, $lines, $header);

            return route('ap.invoices.show', $invoice->id);
        }
        $method = $this->method($c[6]);
        $header['method'] = $method;
        $header['cash_bank_account'] = $options[$method.'_account'];
        if ($party instanceof Customer) {
            $header['receipt_date'] = $this->date($c[1]);
            $receipt = $ar->createReceiptDraft($company, $book, $party, $c[5], $header);

            return route('ar.receipts.show', $receipt->id);
        }
        $header['payment_date'] = $this->date($c[1]);
        $payment = $ap->createPaymentDraft($company, $book, $party, $c[5], $header);

        return route('ap.payments.show', $payment->id);
    }

    private function party(string $kind, Company $company, string $code): Customer|Supplier
    {
        $class = in_array($kind, ['sales', 'receipts'], true) ? Customer::class : Supplier::class;
        $party = $class::query()->where('company_id', $company->id)->where('code', $code)->where('is_active', true)->first();
        if ($party === null || ($party instanceof Supplier && $party->is_blocked)) {
            $this->error(__('imports.party_missing', ['code' => $code]));
        }

        return $party;
    }

    private function account(Company $company, string $code, bool $cash = false, bool $inventory = false): void
    {
        $query = Account::query()->where('company_id', $company->id)->where('code', $code)->where('is_posting', true)->where('is_active', true);
        if ($inventory) {
            $query->where('subledger_mapping', 'INV');
        } else {
            $query->where('is_control', false);
        }
        if ($cash) {
            $query->where(fn ($q) => $q->where('is_bank_account', true)->orWhere('code', 'like', '1101%'));
        }
        if (! $query->exists()) {
            $this->error(__('imports.account_missing', ['code' => $code]));
        }
    }

    private function required(string $value, int $max): string
    {
        if ($value === '' || mb_strlen($value) > $max) {
            $this->error(__('imports.invalid_value'));
        }

        return $value;
    }

    private function number(string $value, bool $positive): void
    {
        if (! preg_match('/^-?\d{1,12}(\.\d{1,6})?$/D', $value) || ($positive && (float) $value <= 0)) {
            $this->error(__('imports.invalid_amount'));
        }
    }

    private function method(string $value): string
    {
        return match ($value) {
            'نقدي', 'نقد', 'cash' => 'cash',
            'تحويل', 'تحويل مصرفي', 'مصرفي', 'bank' => 'bank',
            default => $this->error(__('imports.invalid_method')),
        };
    }

    public function date(string $value): string
    {
        if (preg_match('/^\d{5}(\.0+)?$/D', $value) && (int) $value > 36526 && (int) $value < 73050) {
            return (new DateTimeImmutable('1899-12-30'))->modify('+'.(int) $value.' days')->format('Y-m-d');
        }
        foreach (['!j-n-Y', '!j/n/Y', '!Y-m-d'] as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value);
            $errors = DateTimeImmutable::getLastErrors();
            if ($date !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $date->format('Y-m-d');
            }
        }
        $this->error(__('imports.invalid_date'));
    }

    private function error(string $message): never
    {
        throw ValidationException::withMessages(['import' => $message]);
    }
}
