<?php

namespace Tickets\Rest\Actions;

use Tickets\Actions\PauseTicketWork;
use Tickets\Models\Ticket;

class PauseTicketWorkAction extends TicketTransitionAction
{
    /**
     * @param  array<string, mixed>  $fields
     */
    protected function applyTo(Ticket $ticket, array $fields): void
    {
        app(PauseTicketWork::class)->handle($ticket);
    }
}
