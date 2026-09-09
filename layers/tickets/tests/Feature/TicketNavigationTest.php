<?php

namespace Tickets\Tests\Feature;

use Tickets\Enums\TicketRole;
use Tickets\Tests\TestCase;

/**
 * The navigation must only offer what the reader is allowed to do.
 */
class TicketNavigationTest extends TestCase
{
    public function test_a_requester_is_offered_the_way_to_open_a_ticket(): void
    {
        $this->actingAs($this->userWith(TicketRole::Requester))
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSee(route('tickets.create'));
    }

    public function test_a_manager_is_not_offered_a_way_to_open_a_ticket(): void
    {
        $this->actingAs($this->userWith(TicketRole::Manager))
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertDontSee(route('tickets.create'));
    }

    public function test_a_requester_reads_its_screens_in_the_expected_order(): void
    {
        $this->actingAs($this->userWith(TicketRole::Requester))
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSeeInOrder([
                __('tickets::list.heading_own'),
                __('tickets::form.heading.create'),
                __('tickets::archive.heading'),
            ]);
    }

    public function test_the_support_team_reads_its_screens_in_the_expected_order(): void
    {
        $this->actingAs($this->userWith(TicketRole::Manager))
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSeeInOrder([
                __('tickets::list.heading'),
                __('tickets::archive.heading'),
            ]);
    }

    public function test_a_manager_reaching_the_creation_screen_directly_is_refused(): void
    {
        $this->actingAs($this->userWith(TicketRole::Manager))
            ->get(route('tickets.create'))
            ->assertForbidden();
    }
}
