<?php

declare(strict_types=1);

namespace App\Livewire\Imports;

use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\Account;
use App\Models\SpreadsheetImport;
use App\Services\Accounting\Exceptions\PostingException;
use App\Support\Import\ImportCatalog;
use App\Support\Import\SpreadsheetImporter;
use App\Support\Import\XlsxReader;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.erp')]
class ImportWorkspace extends Component
{
    use InteractsWithAccountingContext;
    use WithFileUploads;

    #[Locked]
    public string $kind = 'customers';

    public ?TemporaryUploadedFile $file = null;

    /** @var array<string, string> */
    public array $options = ['account' => '', 'cash_account' => '', 'bank_account' => '', 'employee_ref' => '', 'inventory_account' => '', 'cogs_account' => ''];

    #[Locked]
    public ?int $batchId = null;

    #[Locked]
    public bool $valid = false;

    #[Locked]
    public int $documentCount = 0;

    public bool $acknowledged = false;

    public int $previewPage = 1;

    public function mount(string $kind = 'customers'): void
    {
        $this->kind = $kind;
        $this->authorizeImport();
    }

    public function updated(string $property): void
    {
        if ($property === 'file' || str_starts_with($property, 'options')) {
            $this->batchId = null;
            $this->valid = false;
            $this->acknowledged = false;
            $this->resetValidation();
        }
    }

    public function preview(XlsxReader $reader, SpreadsheetImporter $importer): void
    {
        $this->authorizeImport();
        $this->resetValidation();
        $this->valid = false;
        $this->acknowledged = false;
        $this->batchId = null;
        $this->previewPage = 1;
        $this->validate(['file' => 'required|file|extensions:xlsx|max:3072', 'options.*' => 'nullable|string|max:100']);
        if ($this->file === null) {
            return;
        }
        $payload = $reader->read($this->file->getRealPath());
        $payload['rows'] = array_values(array_filter($payload['rows'], fn (array $row): bool => ImportCatalog::hasData($this->kind, $row['cells'])));
        $batch = SpreadsheetImport::create([
            'company_id' => $this->requireCompany()->id, 'book_id' => $this->requireBook()->id, 'user_id' => auth()->id(),
            'kind' => $this->kind, 'filename' => mb_substr($this->file->getClientOriginalName(), 0, 255),
            'fingerprint' => hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR)), 'payload' => $payload, 'options' => $this->options,
        ]);
        $this->batchId = $batch->id;
        $groups = $importer->validate($this->kind, $this->requireCompany(), $this->requireBook(), $payload, $this->options);
        $this->documentCount = count($groups);
        $this->valid = true;
    }

    public function confirm(SpreadsheetImporter $importer): void
    {
        $this->authorizeImport();
        $this->validate(['acknowledged' => 'accepted']);
        $batch = $this->batch();
        abort_unless($batch !== null && $this->valid, 403);
        try {
            $importer->commit($batch);
        } catch (ValidationException $exception) {
            $this->valid = false;
            throw $exception;
        } catch (PostingException $exception) {
            $this->addError('import', $exception->getMessage());

            return;
        } catch (QueryException $exception) {
            report($exception);
            $this->addError('import', __('imports.save_failed'));

            return;
        }
        $this->valid = false;
        session()->flash('success', __('imports.saved'));
    }

    public function inspect(int $id): void
    {
        $this->authorizeImport();
        $this->batchId = $id;
        abort_unless($this->batch() !== null, 404);
        $this->valid = false;
        $this->previewPage = 1;
        $this->resetValidation();
    }

    private function authorizeImport(): void
    {
        app(SpreadsheetImporter::class)->authorize($this->kind, $this->requireCompany(), $this->requireBook());
    }

    private function batch(): ?SpreadsheetImport
    {
        return SpreadsheetImport::query()->where('company_id', $this->requireCompany()->id)->where('book_id', $this->requireBook()->id)
            ->where('user_id', auth()->id())->where('kind', $this->kind)->find($this->batchId);
    }

    public function render(): View
    {
        $this->authorizeImport();
        $batch = $this->batch();
        $accounts = Account::query()->where('company_id', $this->requireCompany()->id)->where('is_posting', true)->where('is_active', true)
            ->where(fn ($query) => $query->where('is_control', false)->when($this->kind === 'items', fn ($query) => $query->orWhere('subledger_mapping', 'INV')))->orderBy('code')->get();
        $history = SpreadsheetImport::query()->where('company_id', $this->requireCompany()->id)->where('book_id', $this->requireBook()->id)
            ->where('user_id', auth()->id())->where('kind', $this->kind)->whereNotNull('completed_at')->latest()->limit(20)->get();

        return view('livewire.imports.workspace', compact('batch', 'accounts', 'history'));
    }
}
