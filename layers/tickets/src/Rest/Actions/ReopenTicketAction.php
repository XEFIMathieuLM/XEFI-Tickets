<?php

namespace Tickets\Rest\Actions;

use Tickets\Actions\ReopenTicket;
use Tickets\Models\Ticket;

class ReopenTicketAction extends TicketTransitionAction
{
    /**
     * @param  array<string, mixed>  $fields
     */
    protected function applyTo(Ticket $ticket, array $fields): void
    {
        app(ReopenTicket::class)->handle($ticket);
    }
}
