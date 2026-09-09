<?php

namespace Tickets\Listeners;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Tickets\Enums\TicketPermission;
use Tickets\Events\TicketsEscalated;
use Tickets\Notifications\TicketsEscalatedNotification;

class NotifyManagersOfEscalation
{
    public function handle(TicketsEscalated $event): void
    {
        Notification::send(
            User::permission(TicketPermission::ViewAll->value)->get(),
            new TicketsEscalatedNotification($event->tickets),
        );
    }
}
