<?php

namespace Tickets\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Stands in for the immediate alert raised to whoever oversees the queue.
 */
class ManagerAlertChannel extends UrgencyChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        Log::channel('stack')->critical('ticket.manager_alert', $this->payload($notifiable, $notification));
    }
}
