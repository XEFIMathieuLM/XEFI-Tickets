<?php

namespace Tickets\Http;

use Illuminate\Support\Facades\Gate;
use Tickets\Enums\TicketPermission;

/**
 * Where an account lands once it has signed in. Whoever opens tickets arrives
 * on the screen that opens one; everybody else on the list they work from.
 */
class LandingRoute
{
    public function name(): string
    {
        return Gate::allows(TicketPermission::Create->value)
            ? 'tickets.create'
            : 'tickets.index';
    }
}
