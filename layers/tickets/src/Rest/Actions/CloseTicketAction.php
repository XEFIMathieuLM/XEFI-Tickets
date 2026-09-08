<?php

namespace Tickets\Rest\Actions;

use Tickets\Actions\CloseTicket;
use Tickets\Models\Ticket;

class CloseTicketAction extends TicketTransitionAction
{
    /**
     * @param  array<string, mixed>  $fields
     */
    protected function applyTo(Ticket $ticket, array $fields): void
    {
        app(CloseTicket::class)->handle($ticket);
    }
}
