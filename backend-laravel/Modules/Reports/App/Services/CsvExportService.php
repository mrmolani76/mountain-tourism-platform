<?php

namespace Modules\Reports\App\Services;

/**
 * Build CSV strings with formula-injection escaping for spreadsheet clients.
 */
class CsvExportService
{
    /**
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     */
    public function build(array $headers, array $rows): string
    {
        $lines = [];
        $lines[] = $this->line(array_map(fn ($h) => $this->escapeCell($h), $headers));

        foreach ($rows as $row) {
            $cells = [];
            foreach ($row as $cell) {
                $cells[] = $this->escapeCell($cell);
            }
            $lines[] = $this->line($cells);
        }

        return implode("\n", $lines)."\n";
    }

    /**
     * Escape formula injection: prefix = + - @ with a leading apostrophe.
     */
    public function escapeCell(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $str = (string) $value;
        if ($str !== '' && in_array($str[0], ['=', '+', '-', '@'], true)) {
            return "'".$str;
        }

        return $str;
    }

    /**
     * @param  list<string>  $cells
     */
    private function line(array $cells): string
    {
        return implode(',', array_map(function (string $cell): string {
            if (str_contains($cell, ',') || str_contains($cell, '"') || str_contains($cell, "\n")) {
                return '"'.str_replace('"', '""', $cell).'"';
            }

            return $cell;
        }, $cells));
    }
}
