<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_home_page_sends_a_guest_to_the_sign_in_screen(): void
    {
        $this->get('/')
            ->assertRedirect('/tickets');

        $this->get('/tickets')
            ->assertRedirect(route('login'));
    }

    public function test_the_sign_in_screen_is_reachable(): void
    {
        $this->get(route('login'))->assertOk();
    }
}
