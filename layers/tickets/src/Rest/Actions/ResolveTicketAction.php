<?php

namespace Tickets\Rest\Actions;

use Tickets\Actions\ResolveTicket;
use Tickets\Enums\TicketPermission;
use Tickets\Models\Ticket;

class ResolveTicketAction extends TicketTransitionAction
{
    protected function permission(): TicketPermission
    {
        return TicketPermission::Handle;
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    protected function applyTo(Ticket $ticket, array $fields): void
    {
        app(ResolveTicket::class)->handle($ticket);
    }
}
