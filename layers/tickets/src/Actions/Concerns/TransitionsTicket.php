<?php

namespace Tickets\Actions\Concerns;

use Tickets\Enums\TicketStatus;
use Tickets\Exceptions\IllegalTicketTransition;
use Tickets\Models\Ticket;

/**
 * The single gate every transition goes through. The enum owns the table of
 * legal moves; the action owns the refusal.
 */
trait TransitionsTicket
{
    protected function moveTo(Ticket $ticket, TicketStatus $target): void
    {
        if (! $ticket->status->canTransitionTo($target)) {
            throw IllegalTicketTransition::between($ticket->status, $target);
        }

        $ticket->status = $target;
    }
}
