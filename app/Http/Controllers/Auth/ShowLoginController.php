<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Contracts\View\View;
use Tickets\Enums\TicketRole;

class ShowLoginController
{
    public function __invoke(): View
    {
        return view('auth.login', [
            'accounts' => app()->environment('local') ? $this->seededAccounts() : [],
        ]);
    }

    /**
     * @return array<int, array{name: string, email: string, can: string}>
     */
    private function seededAccounts(): array
    {
        return array_map(
            fn (TicketRole $role): array => [
                'name' => __($role->translationKey()),
                'email' => $role->referenceEmail(),
                'can' => __("tickets::list.profiles.{$role->value}"),
            ],
            TicketRole::cases(),
        );
    }
}
