<?php

namespace Tickets\Console\Commands;

use Illuminate\Console\Command;
use Tickets\Imports\ImportReport;
use Tickets\Jobs\ImportTicketsFromCsv;

class ImportTicketsCommand extends Command
{
    protected $signature = 'tickets:import-csv {path : Path on the disk} {--disk=local} {--queue}';

    protected $description = 'Import tickets from a deposited CSV file';

    public function handle(): int
    {
        $job = new ImportTicketsFromCsv($this->argument('path'), $this->option('disk'));

        if ($this->option('queue')) {
            dispatch($job);

            return self::SUCCESS;
        }

        $this->render($job->run());

        return self::SUCCESS;
    }

    private function render(ImportReport $report): void
    {
        $this->components->info(__('tickets::import.report.read', ['count' => $report->read]));
        $this->components->info(__('tickets::import.report.created', ['count' => $report->created]));

        if ($report->rejected() === 0) {
            return;
        }

        $this->components->warn(__('tickets::import.report.rejected', ['count' => $report->rejected()]));

        foreach ($report->rejections as $line => $reason) {
            $this->components->twoColumnDetail(
                __('tickets::import.report.detail', ['line' => $line, 'reason' => $reason]),
            );
        }
    }
}
