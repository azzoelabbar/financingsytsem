<?php

declare(strict_types=1);

namespace App\Support\Import;

use Illuminate\Validation\ValidationException;
use SimpleXMLElement;
use ZipArchive;

/** Bounded, data-only OOXML reader. Never executes formulas or follows external links. */
final class XlsxReader
{
    /** @return array{headers: list<string>, rows: list<array{row: int, cells: list<string>}>, formulas: bool} */
    public function read(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            $this->invalid();
        }
        try {
            $size = 0;
            if ($zip->numFiles > 200) {
                $this->invalid();
            }
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->statIndex($i);
                if ($entry === false) {
                    $this->invalid();
                }
                $size += $entry['size'];
                // Ignore external-link parts: never read their targets or evaluate formulas.
                if ($size > 24_000_000 || preg_match('/vbaProject|embeddings/i', $entry['name'])) {
                    $this->invalid();
                }
            }
            $workbook = $this->xml($zip, 'xl/workbook.xml');
            if (count($workbook->sheets->sheet) !== 1 || (string) $workbook->workbookPr['date1904'] === '1') {
                $this->invalid();
            }
            $strings = [];
            if ($zip->locateName('xl/sharedStrings.xml') !== false) {
                foreach ($this->xml($zip, 'xl/sharedStrings.xml')->si as $si) {
                    $strings[] = $this->stringValue($si);
                }
            }
            $rows = [];
            $formulas = false;
            foreach ($this->xml($zip, 'xl/worksheets/sheet1.xml')->sheetData->row as $row) {
                if (count($rows) >= 2001) {
                    $this->invalid();
                }
                $cells = [];
                foreach ($row->c as $cell) {
                    if (! preg_match('/^([A-Z]{1,2})[0-9]+$/', (string) $cell['r'], $match)) {
                        $this->invalid();
                    }
                    $column = 0;
                    foreach (str_split($match[1]) as $letter) {
                        $column = $column * 26 + ord($letter) - 64;
                    }
                    if ($column > 32) {
                        $this->invalid();
                    }
                    $formulas = $formulas || isset($cell->f);
                    $value = match ((string) $cell['t']) {
                        's' => $strings[(int) $cell->v] ?? '',
                        'inlineStr' => $this->stringValue($cell->is),
                        'e' => '#ERROR',
                        default => (string) $cell->v,
                    };
                    if (mb_strlen($value) > 1000) {
                        $this->invalid();
                    }
                    $cells[$column - 1] = trim(str_replace("\u{00A0}", ' ', $value));
                }
                $dense = [];
                for ($i = 0; $i <= ($cells === [] ? -1 : max(array_keys($cells))); $i++) {
                    $dense[] = $cells[$i] ?? '';
                }
                $rows[] = ['row' => (int) $row['r'], 'cells' => $dense];
            }
            $header = array_shift($rows);
            if ($header === null || $header['row'] !== 1) {
                $this->invalid();
            }

            return ['headers' => $header['cells'], 'rows' => $rows, 'formulas' => $formulas];
        } finally {
            $zip->close();
        }
    }

    private function xml(ZipArchive $zip, string $name): SimpleXMLElement
    {
        $raw = $zip->getFromName($name);
        if ($raw === false || preg_match('/<!DOCTYPE|<!ENTITY/i', $raw)) {
            $this->invalid();
        }
        $previous = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string($raw, SimpleXMLElement::class, LIBXML_NONET);
            if ($xml === false) {
                $this->invalid();
            }

            return $xml;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function stringValue(SimpleXMLElement $node): string
    {
        if (isset($node->t)) {
            return (string) $node->t;
        }
        $text = '';
        foreach ($node->r as $run) {
            $text .= (string) $run->t;
        }

        return $text;
    }

    private function invalid(): never
    {
        throw ValidationException::withMessages(['file' => __('imports.invalid_file')]);
    }
}
