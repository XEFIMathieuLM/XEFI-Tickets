<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Tickets\Enums\TicketRole;
use Tickets\Http\LandingRoute;

/**
 * One-click sign-in for the seeded demo accounts. Never registered outside a
 * developer machine, and it accepts none but those three addresses.
 */
class SignInAsController
{
    public function __invoke(Request $request): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 403);

        $chosen = $request->validate([
            'email' => ['required', Rule::in($this->referenceEmails())],
        ]);

        Auth::login(User::where('email', $chosen['email'])->sole());

        $request->session()->regenerate();

        return redirect()->route(app(LandingRoute::class)->name());
    }

    /**
     * @return array<int, string>
     */
    private function referenceEmails(): array
    {
        return array_map(
            fn (TicketRole $role): string => $role->referenceEmail(),
            TicketRole::cases(),
        );
    }
}
