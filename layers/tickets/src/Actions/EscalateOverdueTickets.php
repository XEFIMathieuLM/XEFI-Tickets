<?php

namespace Tickets\Actions;

use Illuminate\Database\Eloquent\Builder;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketStatus;
use Tickets\Events\TicketsEscalated;
use Tickets\Models\Ticket;

/**
 * Moves unresolved tickets that outran the target their priority carries up one
 * step. Critical is the ceiling: those are reported, never moved.
 */
class EscalateOverdueTickets
{
    public function handle(): EscalationOutcome
    {
        $overdueIds = $this->overdueIdsByPriority();
        $escalatedIds = $this->raiseEach($overdueIds);

        $outcome = new EscalationOutcome(
            examined: $this->candidates()->count(),
            escalated: Ticket::query()->whereKey($escalatedIds)->get(),
            flagged: count($overdueIds[TicketPriority::Critical->value]),
        );

        if ($outcome->escalated->isNotEmpty()) {
            TicketsEscalated::dispatch($outcome->escalated);
        }

        return $outcome;
    }

    /**
     * One bounded query per priority rather than one per ticket: the target is
     * a constant of the enum, so each priority is its own indexed range scan.
     *
     * @return array<string, array<int, int>>
     */
    private function overdueIdsByPriority(): array
    {
        $found = [];

        foreach (TicketPriority::cases() as $priority) {
            $found[$priority->value] = $this->candidates()
                ->where('priority', $priority)
                ->where('created_at', '<=', now()->subHours($priority->targetHandlingHours()))
                ->pluck('id')
                ->all();
        }

        return $found;
    }

    /**
     * @param  array<string, array<int, int>>  $overdueIds
     * @return array<int, int>
     */
    private function raiseEach(array $overdueIds): array
    {
        $raised = [];

        foreach (TicketPriority::cases() as $priority) {
            $ids = $overdueIds[$priority->value];
            $next = $priority->next();

            if ($ids === [] || $next === null) {
                continue;
            }

            Ticket::query()->whereKey($ids)->toBase()->update([
                'priority' => $next->value,
                'escalated_at' => now(),
            ]);

            $raised = array_merge($raised, $ids);
        }

        return $raised;
    }

    /**
     * @return Builder<Ticket>
     */
    private function candidates(): Builder
    {
        return Ticket::query()
            ->whereNull('escalated_at')
            ->whereNotIn('status', [TicketStatus::Resolved, TicketStatus::Closed]);
    }
}
