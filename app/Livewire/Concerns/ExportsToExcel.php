<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Enums\Ar\DocumentStatus;
use App\Support\Export\ExcelSheet;
use App\Support\Export\XlsxWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Adds a one-click Excel download to a Livewire screen.
 *
 * A screen supplies the sheets it wants written; everything else - the file
 * name, the heading block naming the company/book/period, the RTL sheet view
 * for Arabic - is settled here so every export in the app looks the same.
 *
 * Exports carry the full result set, not the page on screen: components build
 * their own unpaginated query inside {@see excelSheets()}.
 */
trait ExportsToExcel
{
    /** Upper bound on rows pulled into a single export, to keep memory sane. */
    protected const EXPORT_PAGE_SIZE = 5000;

    /**
     * Same filters as the screen, but the whole result set rather than one page.
     *
     * @param  array<string, mixed>  $extra
     */
    protected function exportRequest(array $extra = [], ?string $searchColumns = null): Request
    {
        return $this->listRequest($extra, $searchColumns, perPage: self::EXPORT_PAGE_SIZE, page: 1);
    }

    /**
     * The sheets to write. Implement on every screen that uses this trait.
     *
     * @return list<ExcelSheet>
     */
    abstract protected function excelSheets(): array;

    public function exportExcel(): ?StreamedResponse
    {
        $sheets = $this->excelSheets();

        if ($sheets === []) {
            $this->dispatch('notify', variant: 'info', message: __('erp.export.nothing_to_export'));

            return null;
        }

        $contents = app(XlsxWriter::class)->render($sheets, app()->getLocale() === 'ar');
        $filename = $this->excelFilename();

        return response()->streamDownload(
            static function () use ($contents): void {
                echo $contents;
            },
            $filename,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ],
        );
    }

    /** Slugged screen name plus a timestamp, so repeat downloads never collide. */
    protected function excelFilename(): string
    {
        $base = Str::slug($this->excelTitle());
        $base = $base === '' ? 'export' : $base;

        return $base.'-'.Carbon::now()->format('Ymd-His').'.xlsx';
    }

    /** Human title of the screen, used for the file name and the sheet heading. */
    protected function excelTitle(): string
    {
        return class_basename(static::class);
    }

    /**
     * The company/book/period/generated-at block printed above every table.
     *
     * @param  array<string, mixed>  $extra  Screen-specific lines, e.g. active filters.
     * @return array<string, string|null>
     */
    protected function excelMeta(array $extra = []): array
    {
        $company = $this->company();
        $book = $this->book();
        $period = $this->period();

        $meta = [
            (string) __('erp.company') => $this->localisedName($company),
            (string) __('erp.book') => $this->localisedName($book),
            (string) __('erp.period') => $period === null ? null : (string) $period->period_no,
            (string) __('erp.export.generated_at') => Carbon::now()->format('Y-m-d H:i'),
        ];

        foreach ($extra as $label => $value) {
            $meta[(string) $label] = $this->text($value);
        }

        return array_filter($meta, static fn (?string $value): bool => $value !== null && $value !== '');
    }

    /** Flatten anything a screen hands us for a meta line into plain text. */
    private function text(mixed $value): ?string
    {
        if ($value === null || is_array($value)) {
            return null;
        }

        return is_scalar($value) ? (string) $value : null;
    }

    /**
     * Build a sheet from column definitions, so screens declare what a column
     * is called, how it is formatted, and where its value comes from - once.
     *
     * @param  list<array{0: string, 1: string, 2: callable(mixed): mixed}>  $columns
     * @param  iterable<mixed>  $records
     * @param  array<string, string|null>|null  $meta  Null uses the standard company/book/period block.
     * @param  list<array<int, mixed>>  $totals
     */
    protected function excelSheetFrom(
        string $title,
        array $columns,
        iterable $records,
        ?array $meta = null,
        array $totals = [],
        ?string $heading = null,
    ): ExcelSheet {
        $rows = [];

        foreach ($records as $record) {
            $row = [];

            foreach ($columns as $column) {
                $row[] = ($column[2])($record);
            }

            $rows[] = $row;
        }

        return new ExcelSheet(
            title: $title,
            headings: array_map(static fn (array $column): string => $column[0], $columns),
            rows: $rows,
            formats: array_map(static fn (array $column): string => $column[1], $columns),
            meta: $meta ?? $this->excelMeta(),
            heading: $heading ?? $title,
            totals: $totals,
        );
    }

    /**
     * Build a sheet out of label/value pairs, for screens that present a
     * figure list rather than a table (reconciliations, aging summaries).
     *
     * @param  array<string, mixed>  $values
     * @param  array<string, string|null>|null  $meta
     */
    protected function excelKeyValueSheet(
        string $title,
        array $values,
        string $format = ExcelSheet::MONEY,
        ?array $meta = null,
        ?string $valueHeading = null,
    ): ExcelSheet {
        $rows = [];

        foreach ($values as $label => $value) {
            $rows[] = [(string) $label, $value];
        }

        return new ExcelSheet(
            title: $title,
            headings: [(string) __('erp.description'), $valueHeading ?? (string) __('erp.amount')],
            rows: $rows,
            formats: [ExcelSheet::TEXT, $format],
            meta: $meta ?? $this->excelMeta(),
            heading: $title,
        );
    }

    /**
     * The same words the status badge shows on screen - exports never carry
     * raw enum values, per the plain-language rule for anything user-facing.
     */
    protected function statusLabel(mixed $status): ?string
    {
        if ($status === null || $status === '') {
            return null;
        }

        if ($status instanceof DocumentStatus) {
            return $status->label();
        }

        $raw = is_string($status)
            ? $status
            : (string) ($status instanceof \BackedEnum ? $status->value : '');

        if ($raw === '') {
            return null;
        }

        $enum = DocumentStatus::tryFrom($raw);

        return $enum !== null ? $enum->label() : (string) __('erp.doc_status.'.$raw);
    }

    /** A date rendered the way the screens render it, or null when absent. */
    protected function exportDate(mixed $date): ?string
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        return is_string($date) && $date !== '' ? $date : null;
    }

    /**
     * Localised name of a record that carries the usual name_ar/name_en pair.
     */
    protected function localisedName(?object $record): ?string
    {
        if ($record === null) {
            return null;
        }

        $ar = $record->name_ar ?? null;
        $en = $record->name_en ?? null;

        return app()->getLocale() === 'ar' ? ($ar ?? $en) : ($en ?? $ar);
    }
}
