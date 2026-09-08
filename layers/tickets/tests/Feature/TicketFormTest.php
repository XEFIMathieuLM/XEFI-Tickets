<?php

namespace Tickets\Tests\Feature;

use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketRole;
use Tickets\Enums\TicketStatus;
use Tickets\Livewire\TicketForm;
use Tickets\Models\Ticket;
use Tickets\Notifications\TicketAssignedNotification;
use Tickets\Tests\TestCase;

class TicketFormTest extends TestCase
{
    public function test_it_opens_a_ticket_from_the_interface(): void
    {
        $requester = $this->userWith(TicketRole::Requester);

        Livewire::actingAs($requester)
            ->test(TicketForm::class)
            ->set('title', 'The printer is offline')
            ->set('description', 'Nothing comes out since this morning.')
            ->set('priority', TicketPriority::High->value)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'title' => 'The printer is offline',
            'requester_id' => $requester->id,
            'status' => TicketStatus::Open->value,
            'priority' => TicketPriority::High->value,
        ]);
    }

    public function test_it_edits_an_existing_ticket(): void
    {
        $manager = $this->userWith(TicketRole::Manager);
        $ticket = Ticket::factory()->create(['title' => 'Old title']);

        Livewire::actingAs($manager)
            ->test(TicketForm::class, ['ticket' => $ticket])
            ->assertSet('title', 'Old title')
            ->set('title', 'A much clearer title')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'title' => 'A much clearer title',
        ]);
    }

    public function test_it_refuses_an_empty_form(): void
    {
        Livewire::actingAs($this->userWith(TicketRole::Requester))
            ->test(TicketForm::class)
            ->call('save')
            ->assertHasErrors([
                'title' => 'required',
                'description' => 'required',
                'priority' => 'required',
            ]);

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_it_refuses_a_priority_the_enum_does_not_know(): void
    {
        Livewire::actingAs($this->userWith(TicketRole::Requester))
            ->test(TicketForm::class)
            ->set('title', 'Keyboard broken')
            ->set('description', 'Three keys do not answer.')
            ->set('priority', 'urgentissime')
            ->call('save')
            ->assertHasErrors(['priority']);

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_a_legal_assignment_reports_success_and_notifies(): void
    {
        Notification::fake();

        $technician = $this->userWith(TicketRole::Technician);
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        Livewire::actingAs($this->userWith(TicketRole::Manager))
            ->test(TicketForm::class, ['ticket' => $ticket])
            ->call('assign', $technician->id)
            ->assertHasNoErrors()
            ->assertSet('successMessage', __('tickets::form.feedback.assigned'));

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Assigned->value,
            'assigned_technician_id' => $technician->id,
        ]);

        Notification::assertSentTo($technician, TicketAssignedNotification::class);
    }

    public function test_an_illegal_assignment_reports_a_translated_error_and_stops(): void
    {
        Notification::fake();

        $technician = $this->userWith(TicketRole::Technician);
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Closed]);

        Livewire::actingAs($this->userWith(TicketRole::Manager))
            ->test(TicketForm::class, ['ticket' => $ticket])
            ->call('assign', $technician->id)
            ->assertHasErrors('transition')
            ->assertSet('successMessage', null)
            ->assertOk();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Closed->value,
            'assigned_technician_id' => null,
        ]);

        Notification::assertNothingSent();
    }

    public function test_assigning_does_nothing_while_the_ticket_is_not_saved_yet(): void
    {
        $technician = $this->userWith(TicketRole::Technician);

        Livewire::actingAs($this->userWith(TicketRole::Requester))
            ->test(TicketForm::class)
            ->call('assign', $technician->id)
            ->assertHasNoErrors()
            ->assertSet('successMessage', null);

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_the_page_renders_for_an_authenticated_requester(): void
    {
        $this->actingAs($this->userWith(TicketRole::Requester))
            ->get(route('tickets.create'))
            ->assertOk()
            ->assertSee(__('tickets::form.heading.create'));
    }
}
