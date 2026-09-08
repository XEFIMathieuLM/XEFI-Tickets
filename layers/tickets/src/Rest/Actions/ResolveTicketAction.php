<?php

namespace Tickets\Rest\Actions;

use Tickets\Actions\ResolveTicket;
use Tickets\Models\Ticket;

class ResolveTicketAction extends TicketTransitionAction
{
    /**
     * @param  array<string, mixed>  $fields
     */
    protected function applyTo(Ticket $ticket, array $fields): void
    {
        app(ResolveTicket::class)->handle($ticket);
    }
}
