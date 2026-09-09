<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tickets\Database\Seeders\TicketAccessSeeder;
use Tickets\Enums\TicketRole;

class SignInAsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_signs_in_a_seeded_account_without_a_password(): void
    {
        $requester = User::factory()->create(['email' => TicketRole::Requester->referenceEmail()]);

        $this->post(route('login.as'), ['email' => $requester->email])
            ->assertRedirect(route('tickets.index'));

        $this->assertAuthenticatedAs($requester);
    }

    public function test_each_reference_address_signs_in_its_own_account(): void
    {
        $this->seed(TicketAccessSeeder::class);

        foreach (TicketRole::cases() as $role) {
            $expected = $role === TicketRole::Requester ? 'tickets.create' : 'tickets.index';

            $this->post(route('login.as'), ['email' => $role->referenceEmail()])
                ->assertRedirect(route($expected));

            $this->assertAuthenticatedAs(User::where('email', $role->referenceEmail())->sole());
        }
    }

    public function test_it_refuses_an_address_outside_the_seeded_accounts(): void
    {
        $intruder = User::factory()->create(['email' => 'someone@example.com']);

        $this->from(route('login'))
            ->post(route('login.as'), ['email' => $intruder->email])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
