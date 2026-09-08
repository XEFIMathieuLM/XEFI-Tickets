<?php

namespace Tickets\Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Tickets\Enums\TicketRole;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Ticket;
use Tickets\Notifications\TicketAssignedNotification;
use Tickets\Tests\TestCase;

class TicketTransitionApiTest extends TestCase
{
    private const ACTIONS_URI = '/api/v1/tickets/actions/';

    public function test_it_assigns_a_ticket_through_the_api(): void
    {
        Notification::fake();

        $technician = $this->userWith(TicketRole::Technician);
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        $this->actingAs($this->userWith(TicketRole::Manager))
            ->postJson(self::ACTIONS_URI.'assign-ticket', [
                'resources' => [$ticket->id],
                'fields' => [['name' => 'technician_id', 'value' => $technician->id]],
            ])
            ->assertOk();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Assigned->value,
            'assigned_technician_id' => $technician->id,
        ]);
    }

    public function test_it_answers_409_on_a_transition_absent_from_the_table(): void
    {
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        $this->actingAs($this->userWith(TicketRole::Manager))
            ->postJson(self::ACTIONS_URI.'close-ticket', ['resources' => [$ticket->id]])
            ->assertConflict();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Open->value,
        ]);
    }

    public function test_it_prohibits_moving_the_status_through_the_generic_update(): void
    {
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        $this->actingAs($this->userWith(TicketRole::Manager))
            ->postJson('/api/v1/tickets/mutate', [
                'mutate' => [[
                    'operation' => 'update',
                    'key' => $ticket->id,
                    'attributes' => ['status' => TicketStatus::Closed->value],
                ]],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('mutate.0.attributes.status');
    }

    public function test_it_notifies_the_technician_on_assignment(): void
    {
        Notification::fake();

        $technician = $this->userWith(TicketRole::Technician);
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        $this->actingAs($this->userWith(TicketRole::Manager))
            ->postJson(self::ACTIONS_URI.'assign-ticket', [
                'resources' => [$ticket->id],
                'fields' => [['name' => 'technician_id', 'value' => $technician->id]],
            ])
            ->assertOk();

        Notification::assertSentTo($technician, TicketAssignedNotification::class);
    }

    public function test_it_does_not_notify_anybody_when_the_assignment_is_refused(): void
    {
        Notification::fake();

        $technician = User::factory()->create();
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Closed]);

        $this->actingAs($this->userWith(TicketRole::Manager))
            ->postJson(self::ACTIONS_URI.'assign-ticket', [
                'resources' => [$ticket->id],
                'fields' => [['name' => 'technician_id', 'value' => $technician->id]],
            ])
            ->assertConflict();

        Notification::assertNothingSent();
    }
}
