<?php

namespace Tickets\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Tickets\Models\Ticket;

class TicketAssignedNotification extends Notification
{
    public function __construct(private readonly Ticket $ticket) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('tickets::notifications.assigned.subject', ['title' => $this->ticket->title]))
            ->line(__('tickets::notifications.assigned.greeting'))
            ->line(__('tickets::notifications.assigned.body', [
                'title' => $this->ticket->title,
                'hours' => $this->ticket->priority->targetHandlingHours(),
            ]));
    }
}
