<?php

namespace Tickets\Rest\Actions;

use Tickets\Actions\UnassignTicket;
use Tickets\Models\Ticket;

class UnassignTicketAction extends TicketTransitionAction
{
    /**
     * @param  array<string, mixed>  $fields
     */
    protected function applyTo(Ticket $ticket, array $fields): void
    {
        app(UnassignTicket::class)->handle($ticket);
    }
}
