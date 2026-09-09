<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tickets\Database\Seeders\TicketAccessSeeder;
use Tickets\Enums\TicketRole;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_screen_is_reachable(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_it_signs_in_a_known_account(): void
    {
        $this->knownAccount();

        $this->post(route('login.attempt'), [
            'email' => 'manager@xefi.test',
            'password' => 'password',
        ])->assertRedirect(route('tickets.index'));

        $this->assertAuthenticated();
    }

    public function test_it_refuses_a_wrong_password(): void
    {
        $this->knownAccount();

        $this->from(route('login'))->post(route('login.attempt'), [
            'email' => 'manager@xefi.test',
            'password' => 'not-the-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_it_refuses_an_empty_form(): void
    {
        $this->from(route('login'))->post(route('login.attempt'), [])
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
    }

    public function test_each_reference_address_signs_in_its_own_account(): void
    {
        $this->seed(TicketAccessSeeder::class);

        foreach (TicketRole::cases() as $role) {
            $expected = $role === TicketRole::Requester ? 'tickets.create' : 'tickets.index';

            $this->post(route('login.attempt'), [
                'email' => $role->referenceEmail(),
                'password' => 'password',
            ])->assertRedirect(route($expected));

            $this->assertAuthenticatedAs(User::where('email', $role->referenceEmail())->sole());
        }
    }

    public function test_a_requester_lands_on_the_screen_that_opens_a_ticket(): void
    {
        $this->seed(TicketAccessSeeder::class);

        $this->post(route('login.attempt'), [
            'email' => TicketRole::Requester->referenceEmail(),
            'password' => 'password',
        ])->assertRedirect(route('tickets.create'));
    }

    public function test_the_support_team_lands_on_the_list_it_works_from(): void
    {
        $this->seed(TicketAccessSeeder::class);

        foreach ([TicketRole::Technician, TicketRole::Manager] as $role) {
            $this->post(route('login.attempt'), [
                'email' => $role->referenceEmail(),
                'password' => 'password',
            ])->assertRedirect(route('tickets.index'));
        }
    }

    public function test_the_screen_stays_reachable_to_somebody_already_signed_in(): void
    {
        $this->actingAs($this->knownAccount())
            ->get(route('login'))
            ->assertOk();
    }

    public function test_a_refused_attempt_leaves_the_current_account_signed_in(): void
    {
        $requester = $this->knownAccount('requester@xefi.test');
        $this->knownAccount();

        $this->actingAs($requester)
            ->from(route('login'))
            ->post(route('login.attempt'), [
                'email' => 'manager@xefi.test',
                'password' => 'not-the-password',
            ])
            ->assertSessionHasErrors('email');

        $this->assertAuthenticatedAs($requester);
    }

    public function test_the_offered_accounts_are_named_by_their_french_profile(): void
    {
        $this->app->detectEnvironment(fn (): string => 'local');
        $this->app->setLocale('fr');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Demandeur')
            ->assertSee('Technicien')
            ->assertSee('Responsable');
    }

    public function test_signing_out_sends_the_reader_back_to_the_screen(): void
    {
        $this->actingAs($this->knownAccount())
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    private function knownAccount(string $email = 'manager@xefi.test'): User
    {
        return User::factory()->create([
            'email' => $email,
            'password' => 'password',
        ]);
    }
}
