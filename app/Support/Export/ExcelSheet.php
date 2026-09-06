<?php

declare(strict_types=1);

namespace App\Support\Export;

use DateTimeInterface;

/**
 * One worksheet of an export: a titled table with an optional heading block
 * above it and an optional totals band below it.
 *
 * Values stay raw - numbers as numbers, dates as dates - so Excel can sum and
 * sort them. Presentation (thousands separators, date masks) is the writer's
 * job, driven by the per-column formats declared here.
 */
final class ExcelSheet
{
    public const TEXT = 'text';

    public const NUMBER = 'number';

    public const MONEY = 'money';

    public const DATE = 'date';

    /**
     * @param  string  $title  Sheet tab name.
     * @param  list<string>  $headings  Column headings, one per column.
     * @param  list<array<int, mixed>>  $rows  Body rows, aligned to $headings.
     * @param  list<string>  $formats  Per-column format, one of the class constants.
     * @param  array<string, string|null>  $meta  Label => value lines printed above the table.
     * @param  string|null  $heading  Large title line at the very top.
     * @param  list<array<int, mixed>>  $totals  Rows rendered as a bold totals band.
     */
    public function __construct(
        public readonly string $title,
        public readonly array $headings,
        public readonly array $rows,
        public readonly array $formats = [],
        public readonly array $meta = [],
        public readonly ?string $heading = null,
        public readonly array $totals = [],
    ) {}

    public function formatFor(int $column): string
    {
        return $this->formats[$column] ?? self::TEXT;
    }

    /**
     * Approximate column widths from the widest cell, so nothing lands as ####.
     *
     * @return list<float>
     */
    public function columnWidths(): array
    {
        $widths = [];

        foreach ($this->headings as $index => $heading) {
            $widths[$index] = max(10, min(28, mb_strlen((string) $heading) + 4));
        }

        foreach ([...$this->rows, ...$this->totals] as $row) {
            foreach (array_values($row) as $index => $value) {
                if (! isset($widths[$index])) {
                    continue;
                }

                $widths[$index] = max($widths[$index], min(48, $this->displayWidth($value, $this->formatFor($index))));
            }
        }

        return array_map(static fn (int $width): float => (float) $width, array_values($widths));
    }

    private function displayWidth(mixed $value, string $format): int
    {
        if ($value === null) {
            return 0;
        }

        if ($value instanceof DateTimeInterface || $format === self::DATE) {
            return 14;
        }

        if (is_float($value) || is_int($value)) {
            return mb_strlen(number_format((float) $value, $format === self::MONEY ? 2 : 0)) + 3;
        }

        return mb_strlen((string) $value) + 3;
    }
}
