<?php

namespace Tickets\Notifications;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Tickets\Models\Ticket;

class TicketsEscalatedNotification extends Notification
{
    /**
     * @param  Collection<int, Ticket>  $tickets
     */
    public function __construct(private readonly Collection $tickets) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject(__('tickets::escalation.mail.subject', ['count' => $this->tickets->count()]))
            ->line(__('tickets::escalation.mail.greeting'));

        foreach ($this->tickets as $ticket) {
            $message->line(__('tickets::escalation.mail.line', [
                'title' => $ticket->title,
                'priority' => __($ticket->priority->translationKey()),
            ]));
        }

        return $message;
    }
}
