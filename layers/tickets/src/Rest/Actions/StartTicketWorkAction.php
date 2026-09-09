<?php

namespace Tickets\Rest\Actions;

use Tickets\Actions\StartTicketWork;
use Tickets\Enums\TicketPermission;
use Tickets\Models\Ticket;

class StartTicketWorkAction extends TicketTransitionAction
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
        app(StartTicketWork::class)->handle($ticket);
    }
}
