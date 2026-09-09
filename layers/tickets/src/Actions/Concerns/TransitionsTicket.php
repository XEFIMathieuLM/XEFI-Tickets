<?php

namespace Tickets\Actions\Concerns;

use Tickets\Enums\TicketStatus;
use Tickets\Exceptions\IllegalTicketTransition;
use Tickets\Models\Ticket;

/**
 * The single gate every transition goes through. The enum owns the table of
 * legal moves; the action owns the refusal. A ticket leaving the resolved side
 * of the lifecycle drops its resolution stamp, whichever route it takes.
 */
trait TransitionsTicket
{
    protected function moveTo(Ticket $ticket, TicketStatus $target): void
    {
        if (! $ticket->status->canTransitionTo($target)) {
            throw IllegalTicketTransition::between($ticket->status, $target);
        }

        $ticket->status = $target;

        if (in_array($target, [TicketStatus::Resolved, TicketStatus::Closed], true)) {
            return;
        }

        $ticket->resolved_at = null;
        $ticket->is_resolved_on_time = null;
    }
}
