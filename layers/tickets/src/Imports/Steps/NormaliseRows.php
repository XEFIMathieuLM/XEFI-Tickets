<?php

namespace Tickets\Imports\Steps;

use Closure;
use Illuminate\Support\Str;
use Tickets\Imports\ImportBatch;

/**
 * Smooths what a spreadsheet does to a file before validation judges it.
 */
class NormaliseRows
{
    public function handle(ImportBatch $batch, Closure $next): ImportBatch
    {
        foreach ($batch->rows as $line => $row) {
            $batch->rows[$line] = [
                'title' => Str::squish($row['title']),
                'description' => Str::squish($row['description']),
                'priority' => Str::lower(Str::squish($row['priority'])),
                'requester_email' => Str::lower(Str::squish($row['requester_email'])),
            ];
        }

        return $next($batch);
    }
}
