<?php

namespace Tickets\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Expression;
use Illuminate\Foundation\Queue\Queueable;
use Tickets\Enums\TicketPriority;
use Tickets\Models\Ticket;

/**
 * Decides whether a resolved ticket met the target its priority carries. It
 * takes an identifier and runs after the commit, so it only reads a state the
 * database has accepted.
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

        Ticket::query()
            ->whereKey($ticket->getKey())
            ->toBase()
            ->update(['is_resolved_on_time' => $this->elapsedHoursWithin($ticket->priority)]);
    }

    /**
     * The elapsed time and the comparison are evaluated by the database; only
     * the target crosses over, as a number of hours.
     */
    private function elapsedHoursWithin(TicketPriority $priority): Expression
    {
        return new Expression(sprintf(
            'timestampdiff(HOUR, `created_at`, `resolved_at`) <= %d',
            $priority->targetHandlingHours(),
        ));
    }
}
