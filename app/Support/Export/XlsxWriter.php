<?php

declare(strict_types=1);

namespace App\Support\Export;

use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;
use ZipArchive;

/**
 * Minimal SpreadsheetML (.xlsx) writer.
 *
 * The platform ships no spreadsheet dependency, so this builds the handful of
 * OOXML parts Excel needs from scratch: a workbook, one worksheet per
 * ExcelSheet, and a fixed style table. Strings are written inline, which keeps
 * the writer stateless at the cost of a slightly larger file - fine for the
 * report-sized exports this app produces, and it sidesteps every shared-string
 * encoding pitfall with Arabic text.
 */
final class XlsxWriter
{
    /** Excel's day zero: serial 1 is 1900-01-01, with the 1900 leap-year bug baked in. */
    private const EPOCH = '1899-12-30';

    /** Style ids of the fixed cellXfs table written by stylesXml(). */
    private const S_TITLE = 1;

    private const S_META_LABEL = 2;

    private const S_META_VALUE = 3;

    private const S_HEADER = 4;

    private const S_TEXT = 5;

    private const S_NUMBER = 6;

    private const S_MONEY = 7;

    private const S_DATE = 8;

    private const S_TOTAL_TEXT = 9;

    private const S_TOTAL_NUMBER = 10;

    private const S_TOTAL_MONEY = 11;

    /**
     * Render the workbook and return the raw .xlsx bytes.
     *
     * @param  list<ExcelSheet>  $sheets
     */
    public function render(array $sheets, bool $rightToLeft = false): string
    {
        if ($sheets === []) {
            throw new RuntimeException('An Excel workbook needs at least one sheet.');
        }

        $sheets = array_values($sheets);
        $names = $this->uniqueSheetNames($sheets);

        $path = tempnam(sys_get_temp_dir(), 'mizan-xlsx-');

        if ($path === false) {
            throw new RuntimeException('Could not allocate a temporary file for the Excel export.');
        }

        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not open the Excel export archive for writing.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml(count($sheets)));
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml($names));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml(count($sheets)));
        $zip->addFromString('xl/styles.xml', $this->stylesXml());

        foreach ($sheets as $index => $sheet) {
            $zip->addFromString('xl/worksheets/sheet'.($index + 1).'.xml', $this->sheetXml($sheet, $rightToLeft, $index === 0));
        }

        $zip->close();

        $contents = file_get_contents($path);
        @unlink($path);

        if ($contents === false) {
            throw new RuntimeException('Could not read back the generated Excel export.');
        }

        return $contents;
    }

    /**
     * Excel rejects duplicate or over-long tab names, so settle them up front.
     *
     * @param  list<ExcelSheet>  $sheets
     * @return list<string>
     */
    private function uniqueSheetNames(array $sheets): array
    {
        $names = [];
        $taken = [];

        foreach ($sheets as $index => $sheet) {
            $name = trim((string) preg_replace('#[\\\\/?*\[\]:]#u', ' ', $sheet->title));
            $name = $name === '' ? 'Sheet'.($index + 1) : $name;
            $name = mb_substr($name, 0, 31);

            $candidate = $name;
            $suffix = 2;

            while (isset($taken[mb_strtolower($candidate)])) {
                $tail = ' ('.$suffix.')';
                $candidate = mb_substr($name, 0, 31 - mb_strlen($tail)).$tail;
                $suffix++;
            }

            $taken[mb_strtolower($candidate)] = true;
            $names[] = $candidate;
        }

        return $names;
    }

    private function contentTypesXml(int $sheetCount): string
    {
        $overrides = '';

        for ($i = 1; $i <= $sheetCount; $i++) {
            $overrides .= '<Override PartName="/xl/worksheets/sheet'.$i.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .$overrides
            .'</Types>';
    }

    private function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    /**
     * @param  list<string>  $names
     */
    private function workbookXml(array $names): string
    {
        $sheets = '';

        foreach ($names as $index => $name) {
            $sheets .= '<sheet name="'.$this->escape($name).'" sheetId="'.($index + 1).'" r:id="rId'.($index + 1).'"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets>'.$sheets.'</sheets>'
            .'</workbook>';
    }

    private function workbookRelsXml(int $sheetCount): string
    {
        $rels = '';

        for ($i = 1; $i <= $sheetCount; $i++) {
            $rels .= '<Relationship Id="rId'.$i.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$i.'.xml"/>';
        }

        $rels .= '<Relationship Id="rId'.($sheetCount + 1).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.$rels.'</Relationships>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<numFmts count="3">'
            .'<numFmt numFmtId="164" formatCode="#,##0.00"/>'
            .'<numFmt numFmtId="165" formatCode="yyyy\-mm\-dd"/>'
            .'<numFmt numFmtId="166" formatCode="#,##0"/>'
            .'</numFmts>'
            .'<fonts count="4">'
            .'<font><sz val="11"/><color rgb="FF0F1620"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="14"/><color rgb="FF0F1620"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><color rgb="FF0F1620"/><name val="Calibri"/></font>'
            .'</fonts>'
            .'<fills count="3">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF1F4BB8"/><bgColor indexed="64"/></patternFill></fill>'
            .'</fills>'
            .'<borders count="3">'
            .'<border><left/><right/><top/><bottom/><diagonal/></border>'
            .'<border><left/><right/><top/><bottom style="thin"><color rgb="FFD4DAE3"/></bottom><diagonal/></border>'
            .'<border><left/><right/><top style="thin"><color rgb="FFB5BFCD"/></top><bottom style="double"><color rgb="FFB5BFCD"/></bottom><diagonal/></border>'
            .'</borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="12">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .'<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="2" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="49" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="166" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>'
            .'<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>'
            .'<xf numFmtId="165" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>'
            .'<xf numFmtId="0" fontId="3" fillId="0" borderId="2" xfId="0" applyFont="1" applyBorder="1"/>'
            .'<xf numFmtId="166" fontId="3" fillId="0" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1"/>'
            .'<xf numFmtId="164" fontId="3" fillId="0" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1"/>'
            .'</cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }

    private function sheetXml(ExcelSheet $sheet, bool $rightToLeft, bool $selected): string
    {
        $columnCount = max(1, count($sheet->headings));
        $rowsXml = '';
        $row = 1;

        if ($sheet->heading !== null && $sheet->heading !== '') {
            $rowsXml .= $this->rowXml($row, [$this->inlineCell(1, $row, $sheet->heading, self::S_TITLE)], 22.0);
            $row++;
        }

        foreach ($sheet->meta as $label => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $rowsXml .= $this->rowXml($row, [
                $this->inlineCell(1, $row, (string) $label, self::S_META_LABEL),
                $this->inlineCell(2, $row, (string) $value, self::S_META_VALUE),
            ]);
            $row++;
        }

        if ($rowsXml !== '') {
            $row++; // one blank spacer line between the heading block and the table
        }

        $headerRow = $row;
        $cells = [];

        foreach ($sheet->headings as $index => $heading) {
            $cells[] = $this->inlineCell($index + 1, $row, (string) $heading, self::S_HEADER);
        }

        $rowsXml .= $this->rowXml($row, $cells, 22.0);
        $row++;

        $firstBodyRow = $row;

        foreach ($sheet->rows as $values) {
            $cells = [];
            $values = array_values($values);

            foreach ($values as $index => $value) {
                $cells[] = $this->valueCell($index + 1, $row, $value, $sheet->formatFor($index), false);
            }

            $rowsXml .= $this->rowXml($row, $cells);
            $row++;
        }

        $lastBodyRow = max($firstBodyRow, $row - 1);

        foreach ($sheet->totals as $values) {
            $cells = [];
            $values = array_values($values);

            foreach ($values as $index => $value) {
                $cells[] = $this->valueCell($index + 1, $row, $value, $sheet->formatFor($index), true);
            }

            $rowsXml .= $this->rowXml($row, $cells);
            $row++;
        }

        $lastColumn = $this->columnName($columnCount);

        $panes = '<pane ySplit="'.$headerRow.'" topLeftCell="A'.$firstBodyRow.'" activePane="bottomLeft" state="frozen"/>'
            .'<selection pane="bottomLeft" activeCell="A'.$firstBodyRow.'" sqref="A'.$firstBodyRow.'"/>';

        $autoFilter = $sheet->rows !== []
            ? '<autoFilter ref="A'.$headerRow.':'.$lastColumn.$lastBodyRow.'"/>'
            : '';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetViews><sheetView'.($rightToLeft ? ' rightToLeft="1"' : '').' showGridLines="0"'.($selected ? ' tabSelected="1"' : '').' workbookViewId="0">'.$panes.'</sheetView></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="16"/>'
            .$this->columnsXml($sheet)
            .'<sheetData>'.$rowsXml.'</sheetData>'
            .$autoFilter
            .'</worksheet>';
    }

    private function columnsXml(ExcelSheet $sheet): string
    {
        $widths = $sheet->columnWidths();

        if ($widths === []) {
            return '';
        }

        $cols = '';

        foreach ($widths as $index => $width) {
            $cols .= '<col min="'.($index + 1).'" max="'.($index + 1).'" width="'.$width.'" customWidth="1"/>';
        }

        return '<cols>'.$cols.'</cols>';
    }

    /**
     * @param  list<string>  $cells
     */
    private function rowXml(int $row, array $cells, ?float $height = null): string
    {
        $attrs = ' r="'.$row.'"';

        if ($height !== null) {
            $attrs .= ' ht="'.$height.'" customHeight="1"';
        }

        return '<row'.$attrs.'>'.implode('', $cells).'</row>';
    }

    private function inlineCell(int $column, int $row, string $value, int $style): string
    {
        return '<c r="'.$this->columnName($column).$row.'" s="'.$style.'" t="inlineStr"><is><t xml:space="preserve">'.$this->escape($value).'</t></is></c>';
    }

    private function valueCell(int $column, int $row, mixed $value, string $format, bool $isTotal): string
    {
        $ref = $this->columnName($column).$row;

        if ($value === null || $value === '') {
            return '<c r="'.$ref.'" s="'.($isTotal ? self::S_TOTAL_TEXT : self::S_TEXT).'"/>';
        }

        if ($value instanceof DateTimeInterface || $format === 'date') {
            $serial = $this->dateSerial($value);

            if ($serial !== null) {
                return '<c r="'.$ref.'" s="'.self::S_DATE.'"><v>'.$serial.'</v></c>';
            }
        }

        if ($format === 'money' || $format === 'number') {
            $numeric = $this->numeric($value);

            if ($numeric !== null) {
                $style = $format === 'money'
                    ? ($isTotal ? self::S_TOTAL_MONEY : self::S_MONEY)
                    : ($isTotal ? self::S_TOTAL_NUMBER : self::S_NUMBER);

                return '<c r="'.$ref.'" s="'.$style.'"><v>'.$numeric.'</v></c>';
            }
        }

        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        return $this->inlineCell($column, $row, (string) $value, $isTotal ? self::S_TOTAL_TEXT : self::S_TEXT);
    }

    /** Numbers arrive as Decimal-formatted strings, so normalise before writing. */
    private function numeric(mixed $value): ?string
    {
        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            return is_finite($value) ? $this->trimZeros(sprintf('%.6F', $value)) : null;
        }

        if (! is_string($value)) {
            return null;
        }

        $clean = str_replace([',', ' ', "\u{00a0}"], '', trim($value));

        if ($clean === '' || ! is_numeric($clean)) {
            return null;
        }

        return $this->trimZeros(sprintf('%.6F', (float) $clean));
    }

    private function trimZeros(string $value): string
    {
        $trimmed = rtrim(rtrim($value, '0'), '.');

        return $trimmed === '' || $trimmed === '-' ? '0' : $trimmed;
    }

    private function dateSerial(mixed $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            $date = DateTimeImmutable::createFromInterface($value);
        } elseif (is_string($value) && trim($value) !== '') {
            $timestamp = strtotime($value);

            if ($timestamp === false) {
                return null;
            }

            $date = (new DateTimeImmutable)->setTimestamp($timestamp);
        } else {
            return null;
        }

        $epoch = new DateTimeImmutable(self::EPOCH.' 00:00:00', $date->getTimezone());
        $days = (float) $epoch->diff($date)->days;
        $fraction = ((int) $date->format('H') * 3600 + (int) $date->format('i') * 60 + (int) $date->format('s')) / 86400;

        return $this->trimZeros(sprintf('%.6F', $days + $fraction));
    }

    private function columnName(int $column): string
    {
        $name = '';

        while ($column > 0) {
            $remainder = ($column - 1) % 26;
            $name = chr(65 + $remainder).$name;
            $column = intdiv($column - 1 - $remainder, 26);
        }

        return $name === '' ? 'A' : $name;
    }

    private function escape(string $value): string
    {
        // Control characters other than tab/newline are illegal in XML 1.0.
        $value = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value);

        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
