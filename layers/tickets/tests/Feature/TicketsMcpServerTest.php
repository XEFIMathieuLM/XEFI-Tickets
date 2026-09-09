<?php

namespace Tickets\Tests\Feature;

use Illuminate\Support\Facades\Notification;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketRole;
use Tickets\Enums\TicketStatus;
use Tickets\Mcp\Resources\TicketInputRules;
use Tickets\Mcp\Servers\TicketsServer;
use Tickets\Mcp\Tools\AssignTicketTool;
use Tickets\Mcp\Tools\OpenTicketTool;
use Tickets\Mcp\Tools\SearchTicketsTool;
use Tickets\Mcp\Tools\ShowTicketTool;
use Tickets\Models\Ticket;
use Tickets\Tests\TestCase;

class TicketsMcpServerTest extends TestCase
{
    public function test_a_requester_only_finds_its_own_tickets(): void
    {
        $requester = $this->userWith(TicketRole::Requester);
        Ticket::factory()->create(['requester_id' => $requester->id, 'title' => 'Mine to see']);
        Ticket::factory()->create(['title' => 'Somebody else problem']);

        TicketsServer::actingAs($requester)
            ->tool(SearchTicketsTool::class)
            ->assertOk()
            ->assertSee('Mine to see')
            ->assertDontSee('Somebody else problem');
    }

    public function test_a_manager_finds_every_ticket(): void
    {
        Ticket::factory()->create(['title' => 'Somebody else problem']);

        TicketsServer::actingAs($this->userWith(TicketRole::Manager))
            ->tool(SearchTicketsTool::class)
            ->assertOk()
            ->assertSee('Somebody else problem');
    }

    public function test_reading_a_ticket_out_of_reach_returns_a_usable_error(): void
    {
        $ticket = Ticket::factory()->create();

        TicketsServer::actingAs($this->userWith(TicketRole::Requester))
            ->tool(ShowTicketTool::class, ['ticket_id' => $ticket->id])
            ->assertHasErrors()
            ->assertSee((string) $ticket->id);
    }

    public function test_opening_a_ticket_goes_through_the_domain_action(): void
    {
        $requester = $this->userWith(TicketRole::Requester);

        TicketsServer::actingAs($requester)
            ->tool(OpenTicketTool::class, [
                'title' => 'Keyboard dead',
                'description' => 'No key answers since the update.',
            ])
            ->assertOk();

        $this->assertDatabaseHas('tickets', [
            'title' => 'Keyboard dead',
            'requester_id' => $requester->id,
            'status' => TicketStatus::Open->value,
            'priority' => TicketPriority::default()->value,
        ]);
    }

    public function test_a_manager_may_not_open_a_ticket(): void
    {
        TicketsServer::actingAs($this->userWith(TicketRole::Manager))
            ->tool(OpenTicketTool::class, [
                'title' => 'Not allowed',
                'description' => 'The manager profile does not carry that permission.',
            ])
            ->assertHasErrors();

        $this->assertDatabaseMissing('tickets', ['title' => 'Not allowed']);
    }

    public function test_handing_a_closed_ticket_over_is_explained_to_the_agent(): void
    {
        Notification::fake();

        $technician = $this->userWith(TicketRole::Technician);
        $closed = Ticket::factory()->create(['status' => TicketStatus::Closed]);

        TicketsServer::actingAs($this->userWith(TicketRole::Manager))
            ->tool(AssignTicketTool::class, [
                'ticket_id' => $closed->id,
                'technician_id' => $technician->id,
            ])
            ->assertHasErrors()
            ->assertSee('closed');

        $this->assertDatabaseHas('tickets', [
            'id' => $closed->id,
            'status' => TicketStatus::Closed->value,
            'assigned_technician_id' => null,
        ]);
    }

    public function test_an_assignment_asked_by_the_agent_goes_through(): void
    {
        Notification::fake();

        $technician = $this->userWith(TicketRole::Technician);
        $open = Ticket::factory()->create(['status' => TicketStatus::Open]);

        TicketsServer::actingAs($this->userWith(TicketRole::Manager))
            ->tool(AssignTicketTool::class, [
                'ticket_id' => $open->id,
                'technician_id' => $technician->id,
            ])
            ->assertOk();

        $this->assertDatabaseHas('tickets', [
            'id' => $open->id,
            'status' => TicketStatus::Open->value,
            'assigned_technician_id' => $technician->id,
        ]);
    }

    public function test_the_input_rules_resource_tells_the_agent_what_to_fill(): void
    {
        TicketsServer::actingAs($this->userWith(TicketRole::Requester))
            ->resource(TicketInputRules::class)
            ->assertOk()
            ->assertSee([TicketPriority::Critical->value, TicketStatus::InProgress->value, 'target_handling_hours']);
    }

    public function test_a_tool_refuses_input_its_schema_does_not_accept(): void
    {
        TicketsServer::actingAs($this->userWith(TicketRole::Requester))
            ->tool(OpenTicketTool::class, [
                'description' => 'The title is missing.',
            ])
            ->assertHasErrors();
    }
}
