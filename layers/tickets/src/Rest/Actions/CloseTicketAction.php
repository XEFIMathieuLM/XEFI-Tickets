<?php

namespace Tickets\Rest\Actions;

use Tickets\Actions\CloseTicket;
use Tickets\Enums\TicketPermission;
use Tickets\Models\Ticket;

class CloseTicketAction extends TicketTransitionAction
{
    protected function permission(): TicketPermission
    {
        return TicketPermission::Close;
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    protected function applyTo(Ticket $ticket, array $fields): void
    {
        app(CloseTicket::class)->handle($ticket);
    }
}
