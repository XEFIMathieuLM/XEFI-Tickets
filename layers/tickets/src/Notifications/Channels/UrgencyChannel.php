<?php

namespace Tickets\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Stands in for the paging system. A structured line is enough: the point is
 * that the channel is chosen, not that a pager rings.
 */
class UrgencyChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        Log::channel('stack')->warning('ticket.urgency', $this->payload($notifiable, $notification));
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(object $notifiable, Notification $notification): array
    {
        return [
            'notifiable' => $notifiable::class,
            'notifiable_id' => $notifiable->getKey(),
            'notification' => $notification::class,
        ];
    }
}
