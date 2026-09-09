<?php

namespace Tickets\Tests\Feature;

use Livewire\Livewire;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketRole;
use Tickets\Livewire\TicketForm;
use Tickets\Models\Ticket;
use Tickets\Tests\TestCase;

/**
 * Whoever opens a ticket does not weigh it: the support team does, once it has
 * read the request.
 */
class TicketImportanceTest extends TestCase
{
    public function test_a_requester_is_never_offered_the_importance(): void
    {
        Livewire::actingAs($this->userWith(TicketRole::Requester))
            ->test(TicketForm::class)
            ->assertDontSee(__('tickets::form.labels.priority'));
    }

    public function test_a_ticket_opened_by_a_requester_lands_on_the_default(): void
    {
        $requester = $this->userWith(TicketRole::Requester);

        Livewire::actingAs($requester)
            ->test(TicketForm::class)
            ->set('title', 'Screen flickers')
            ->set('description', 'It blinks every few seconds.')
            ->set('priority', TicketPriority::Critical->value)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'title' => 'Screen flickers',
            'priority' => TicketPriority::default()->value,
        ]);
    }

    public function test_the_support_team_weighs_the_ticket(): void
    {
        $ticket = Ticket::factory()->create(['priority' => TicketPriority::Low]);

        Livewire::actingAs($this->userWith(TicketRole::Manager))
            ->test(TicketForm::class, ['ticket' => $ticket])
            ->assertSee(__('tickets::form.labels.priority'))
            ->set('priority', TicketPriority::Critical->value)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'priority' => TicketPriority::Critical->value,
        ]);
    }

    public function test_a_requester_editing_its_ticket_leaves_the_importance_alone(): void
    {
        $requester = $this->userWith(TicketRole::Requester);
        $ticket = Ticket::factory()->create([
            'requester_id' => $requester->id,
            'priority' => TicketPriority::Critical,
        ]);

        Livewire::actingAs($requester)
            ->test(TicketForm::class, ['ticket' => $ticket])
            ->set('title', 'A clearer title from the requester')
            ->set('priority', TicketPriority::Low->value)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'title' => 'A clearer title from the requester',
            'priority' => TicketPriority::Critical->value,
        ]);
    }

    public function test_it_refuses_an_importance_the_enum_does_not_know(): void
    {
        $ticket = Ticket::factory()->create(['priority' => TicketPriority::Low]);

        Livewire::actingAs($this->userWith(TicketRole::Manager))
            ->test(TicketForm::class, ['ticket' => $ticket])
            ->set('priority', 'urgentissime')
            ->call('save')
            ->assertHasErrors(['priority']);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'priority' => TicketPriority::Low->value,
        ]);
    }
}
