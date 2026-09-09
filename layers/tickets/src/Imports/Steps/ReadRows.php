<?php

namespace Tickets\Imports\Steps;

use Closure;
use Tickets\Imports\ImportBatch;

/**
 * Turns the raw contents into rows keyed by their source line number, so a
 * rejection can always name the line the reader will look at.
 */
class ReadRows
{
    private const COLUMNS = ['title', 'description', 'priority', 'requester_email'];

    public function handle(ImportBatch $batch, Closure $next): ImportBatch
    {
        $lines = preg_split('/\R/', trim($batch->contents)) ?: [];
        $header = null;
        $line = 0;

        foreach ($lines as $rawLine) {
            $line++;

            if ($rawLine === '') {
                continue;
            }

            $values = str_getcsv($rawLine, ',', '"', '');

            if ($header === null) {
                $header = $values;

                continue;
            }

            $batch->read++;
            $batch->rows[$line] = $this->combine($header, $values);
        }

        return $next($batch);
    }

    /**
     * @param  array<int, string|null>  $header
     * @param  array<int, string|null>  $values
     * @return array<string, string>
     */
    private function combine(array $header, array $values): array
    {
        $row = [];

        foreach (self::COLUMNS as $column) {
            $position = array_search($column, $header, true);
            $row[$column] = $position === false ? '' : trim((string) ($values[$position] ?? ''));
        }

        return $row;
    }
}
