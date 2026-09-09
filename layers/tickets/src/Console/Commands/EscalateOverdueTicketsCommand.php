<?php

namespace Tickets\Console\Commands;

use Illuminate\Console\Command;
use Tickets\Actions\EscalateOverdueTickets;

class EscalateOverdueTicketsCommand extends Command
{
    protected $signature = 'tickets:escalate-overdue';

    protected $description = 'Raise the priority of unresolved tickets that outran their target';

    public function handle(EscalateOverdueTickets $escalate): int
    {
        $outcome = $escalate->handle();

        $this->components->info(__('tickets::escalation.report.examined', [
            'count' => $outcome->examined,
        ]));
        $this->components->info(__('tickets::escalation.report.escalated', [
            'count' => $outcome->escalated->count(),
        ]));

        if ($outcome->flagged > 0) {
            $this->components->warn(__('tickets::escalation.report.flagged', [
                'count' => $outcome->flagged,
            ]));
        }

        return self::SUCCESS;
    }
}
