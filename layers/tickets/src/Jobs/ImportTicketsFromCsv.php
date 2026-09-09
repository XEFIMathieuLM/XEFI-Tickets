<?php

namespace Tickets\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Storage;
use Tickets\Events\TicketImportFinished;
use Tickets\Exceptions\ImportSourceUnreadable;
use Tickets\Imports\ImportBatch;
use Tickets\Imports\ImportReport;
use Tickets\Imports\Steps\CreateTickets;
use Tickets\Imports\Steps\NormaliseRows;
use Tickets\Imports\Steps\ReadRows;
use Tickets\Imports\Steps\ResolveRequesters;
use Tickets\Imports\Steps\ValidateRows;

/**
 * Runs the ordered steps over one deposited file. A missing or unreadable
 * source is a technical failure and stops the import.
 */
class ImportTicketsFromCsv implements ShouldQueue
{
    use Queueable;

    public const DISK = 'local';

    public function __construct(
        private readonly string $path,
        private readonly string $disk = self::DISK,
    ) {}

    public function handle(): void
    {
        TicketImportFinished::dispatch($this->run());
    }

    public function run(): ImportReport
    {
        $batch = app(Pipeline::class)
            ->send(new ImportBatch($this->contents()))
            ->through([
                ReadRows::class,
                NormaliseRows::class,
                ValidateRows::class,
                ResolveRequesters::class,
                CreateTickets::class,
            ])
            ->thenReturn();

        return $batch->report();
    }

    private function contents(): string
    {
        $disk = Storage::disk($this->disk);

        if (! $disk->exists($this->path)) {
            throw ImportSourceUnreadable::at($this->path);
        }

        return (string) $disk->get($this->path);
    }
}
