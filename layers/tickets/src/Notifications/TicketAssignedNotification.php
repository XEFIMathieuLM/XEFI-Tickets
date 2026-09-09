<?php

namespace Tickets\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Tickets\Models\Ticket;
use Tickets\Notifications\Policies\NotificationPolicyResolver;

class TicketAssignedNotification extends Notification
{
    public function __construct(private readonly Ticket $ticket) {}

    /**
     * The channels come from the priority, through a policy this class never
     * inspects: no condition here, and none at the call site either.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return app(NotificationPolicyResolver::class)->for($this->ticket->priority)->channels();
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
