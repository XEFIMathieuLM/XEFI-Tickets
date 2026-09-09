<?php

namespace Tickets\Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tickets\Enums\TicketRole;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Ticket;
use Tickets\Tests\TestCase;

/**
 * Walks every endpoint of the lifecycle through the API, so each one is proven
 * reachable and not only the two the other suite uses.
 */
class TicketTransitionCoverageTest extends TestCase
{
    private const ACTIONS_URI = '/api/v1/tickets/actions/%s';

    public function test_every_endpoint_of_the_lifecycle_is_reachable_through_the_api(): void
    {
        Notification::fake();

        $technician = $this->userWith(TicketRole::Technician);
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        $this->actingAs($this->userWith(TicketRole::Manager));

        $this->assertStatusAfter('assign-ticket', $ticket, TicketStatus::Open, $technician);
        $this->assertStatusAfter('start-ticket-work', $ticket, TicketStatus::InProgress);
        $this->assertStatusAfter('unassign-ticket', $ticket, TicketStatus::Open);
        $this->assertStatusAfter('resolve-ticket', $ticket, TicketStatus::Resolved);
        $this->assertStatusAfter('start-ticket-work', $ticket, TicketStatus::InProgress);
        $this->assertStatusAfter('resolve-ticket', $ticket, TicketStatus::Resolved);
        $this->assertStatusAfter('close-ticket', $ticket, TicketStatus::Closed);
    }

    public function test_a_transition_without_named_resources_is_refused(): void
    {
        Ticket::factory()->create(['status' => TicketStatus::Open]);

        $this->actingAs($this->userWith(TicketRole::Manager))
            ->postJson(sprintf(self::ACTIONS_URI, 'close-ticket'), [])
            ->assertUnprocessable();
    }

    private function assertStatusAfter(
        string $uriKey,
        Ticket $ticket,
        TicketStatus $expected,
        ?User $technician = null,
    ): TestResponse {
        $payload = ['resources' => [$ticket->id]];

        if ($technician !== null) {
            $payload['fields'] = [['name' => 'technician_id', 'value' => $technician->id]];
        }

        $response = $this->postJson(sprintf(self::ACTIONS_URI, $uriKey), $payload)->assertOk();

        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => $expected->value]);

        return $response;
    }
}
