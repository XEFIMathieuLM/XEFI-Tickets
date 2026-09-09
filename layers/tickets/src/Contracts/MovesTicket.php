<?php

namespace Tickets\Contracts;

use Tickets\Models\Ticket;

/**
 * A move of the lifecycle that needs nothing but the ticket, so a screen can
 * offer it as a single button and a caller can run it without extra input.
 */
interface MovesTicket
{
    public function handle(Ticket $ticket): Ticket;
}
