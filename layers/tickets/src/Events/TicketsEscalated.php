<?php

namespace Tickets\Events;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Tickets\Models\Ticket;

class TicketsEscalated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  Collection<int, Ticket>  $tickets
     */
    public function __construct(public readonly Collection $tickets) {}
}
