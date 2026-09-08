<?php

namespace Tickets\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Tickets\Models\Ticket;

/**
 * Decides whether a resolved ticket met the target its priority carries.
 *
 * The job receives an identifier rather than a model, and is dispatched after
 * the commit, so it can only ever read a state the database has accepted.
 */
class EvaluateResolutionDelay implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly int $ticketId) {}

    public function handle(): void
    {
        $ticket = Ticket::query()->whereKey($this->ticketId)->first();

        if ($ticket === null || $ticket->resolved_at === null) {
            return;
        }

        // The elapsed time and the comparison are evaluated by the database:
        // no row is pulled into PHP to be measured. Only the target itself
        // comes from the enum, as an integer number of hours.
        Ticket::query()
            ->whereKey($ticket->getKey())
            ->toBase()
            ->update([
                'is_resolved_on_time' => DB::raw(sprintf(
                    'timestampdiff(HOUR, `created_at`, `resolved_at`) <= %d',
                    $ticket->priority->targetHandlingHours(),
                )),
            ]);
    }
}
