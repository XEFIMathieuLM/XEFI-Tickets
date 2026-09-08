<?php

namespace Tickets\Listeners;

use Tickets\Events\TicketAssigned;
use Tickets\Notifications\TicketAssignedNotification;

class NotifyAssignedTechnician
{
    public function handle(TicketAssigned $event): void
    {
        $event->technician->notify(new TicketAssignedNotification($event->ticket));
    }
}
