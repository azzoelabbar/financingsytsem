<?php

declare(strict_types=1);

namespace App\Livewire\Books;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class Index extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $books = $company
            ? app(DomainApplicationService::class)->listBooks($company)
            : [];

        if ($this->search !== '') {
            $needle = mb_strtolower($this->search);
            $books = array_values(array_filter($books, function ($book) use ($needle): bool {
                $haystack = mb_strtolower(implode(' ', array_filter([
                    $book->code,
                    $book->name_ar,
                    $book->name_en,
                ])));

                return str_contains($haystack, $needle);
            }));
        }

        return view('livewire.books.index', [
            'books' => $books,
            'company' => $company,
            'currentBook' => $this->book(),
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.nav.books');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();

        if ($company === null) {
            return [];
        }

        $books = app(DomainApplicationService::class)->listBooks($company);
        $current = $this->book();

        return [$this->excelSheetFrom(
            __('erp.nav.books'),
            [
                [__('erp.code'), ExcelSheet::TEXT, fn ($b) => $b->code],
                [__('erp.name'), ExcelSheet::TEXT, fn ($b) => $this->localisedName($b)],
                [__('erp.report_basis'), ExcelSheet::TEXT, fn ($b) => match ($b->code) {
                    'IFRS' => __('erp.book_basis.ifrs'),
                    'TAX' => __('erp.book_basis.tax'),
                    'LOCAL' => __('erp.book_basis.local'),
                    default => $b->code,
                }],
                [__('erp.books.current'), ExcelSheet::TEXT, fn ($b) => $current?->id === $b->id
                    ? __('erp.books.current')
                    : null],
            ],
            $books,
        )];
    }
}
