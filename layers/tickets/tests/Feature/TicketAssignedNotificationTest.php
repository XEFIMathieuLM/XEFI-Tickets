<?php

namespace Tickets\Tests\Feature;

use App\Models\User;
use Tickets\Enums\TicketPriority;
use Tickets\Models\Ticket;
use Tickets\Notifications\TicketAssignedNotification;
use Tickets\Tests\TestCase;

class TicketAssignedNotificationTest extends TestCase
{
    public function test_a_quiet_priority_goes_out_by_mail_alone(): void
    {
        $ticket = Ticket::factory()->create(['priority' => TicketPriority::Low]);

        $notification = new TicketAssignedNotification($ticket);

        $this->assertSame(['mail'], $notification->via(User::factory()->create()));
    }

    public function test_the_mail_names_the_ticket_and_the_target_its_priority_carries(): void
    {
        $ticket = Ticket::factory()->create([
            'title' => 'Router down in the meeting room',
            'priority' => TicketPriority::Critical,
        ]);

        $mail = (new TicketAssignedNotification($ticket))->toMail(User::factory()->create());

        $this->assertSame(
            __('tickets::notifications.assigned.subject', ['title' => $ticket->title]),
            $mail->subject,
        );
        $this->assertContains(
            __('tickets::notifications.assigned.body', ['title' => $ticket->title, 'hours' => 2]),
            $mail->introLines,
        );
    }
}
