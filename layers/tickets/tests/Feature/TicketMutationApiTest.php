<?php

namespace Tickets\Tests\Feature;

use App\Models\User;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketRole;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Ticket;
use Tickets\Tests\TestCase;

class TicketMutationApiTest extends TestCase
{
    private const MUTATE_URI = '/api/v1/tickets/mutate';

    private const DESTROY_URI = '/api/v1/tickets';

    public function test_it_creates_a_ticket(): void
    {
        $requester = $this->userWith(TicketRole::Requester);

        $response = $this->actingAs($requester)->postJson(self::MUTATE_URI, [
            'mutate' => [$this->creationPayload($requester, [
                'title' => 'Laptop will not boot',
                'description' => 'Black screen since this morning.',
                'status' => TicketStatus::Open->value,
                'priority' => TicketPriority::High->value,
            ])],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('tickets', [
            'title' => 'Laptop will not boot',
            'requester_id' => $requester->id,
            'status' => TicketStatus::Open->value,
            'priority' => TicketPriority::High->value,
        ]);
    }

    public function test_it_refuses_a_ticket_without_a_title(): void
    {
        $requester = $this->userWith(TicketRole::Requester);

        $response = $this->actingAs($requester)->postJson(self::MUTATE_URI, [
            'mutate' => [$this->creationPayload($requester, [
                'description' => 'No title supplied.',
                'status' => TicketStatus::Open->value,
                'priority' => TicketPriority::High->value,
            ])],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('mutate.0.attributes.title');
    }

    public function test_it_refuses_an_unknown_status(): void
    {
        $requester = $this->userWith(TicketRole::Requester);

        $response = $this->actingAs($requester)->postJson(self::MUTATE_URI, [
            'mutate' => [$this->creationPayload($requester, [
                'title' => 'Mouse not working',
                'description' => 'Nothing happens when clicking.',
                'status' => 'sleeping',
                'priority' => TicketPriority::Low->value,
            ])],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('mutate.0.attributes.status');
    }

    public function test_it_updates_a_ticket(): void
    {
        $ticket = Ticket::factory()->create(['title' => 'Screen flickers']);

        $response = $this->actingAs($this->userWith(TicketRole::Manager))->postJson(self::MUTATE_URI, [
            'mutate' => [[
                'operation' => 'update',
                'key' => $ticket->id,
                'attributes' => ['title' => 'Screen flickers on the docking station'],
            ]],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'title' => 'Screen flickers on the docking station',
        ]);
    }

    public function test_it_soft_deletes_a_ticket(): void
    {
        $ticket = Ticket::factory()->create();

        $this->actingAs($this->userWith(TicketRole::Manager))
            ->deleteJson(self::DESTROY_URI, ['resources' => [$ticket->id]])
            ->assertOk();

        $this->assertSoftDeleted($ticket);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function creationPayload(User $requester, array $attributes): array
    {
        return [
            'operation' => 'create',
            'attributes' => $attributes,
            'relations' => [
                'requester' => ['operation' => 'attach', 'key' => $requester->id],
            ],
        ];
    }
}
