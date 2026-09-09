<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Tickets\Models\Ticket;

Broadcast::channel('tickets.{ticket}', function (User $user, Ticket $ticket): bool {
    return $user->can('view', $ticket);
});
