<?php

namespace Tickets\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tickets\Enums\TicketPermission;
use Tickets\Enums\TicketRole;

/**
 * Ships the access rules of the ticketing layer: the permissions the code
 * checks, the roles that group them, and one reference account per profile.
 */
class TicketAccessSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(PermissionRegistrar $registrar): void
    {
        $registrar->forgetCachedPermissions();

        foreach (TicketPermission::cases() as $permission) {
            Permission::findOrCreate($permission->value);
        }

        // Spatie resolves permission names against its cache: without this flush,
        // syncPermissions() below would not see the ones just created.
        $registrar->forgetCachedPermissions();

        foreach (TicketRole::cases() as $role) {
            $this->createRole($role);
            $this->createReferenceUser($role);
        }

        $registrar->forgetCachedPermissions();
    }

    private function createRole(TicketRole $role): void
    {
        Role::findOrCreate($role->value)->syncPermissions(
            array_map(
                fn (TicketPermission $permission): string => $permission->value,
                $role->permissions(),
            ),
        );
    }

    private function createReferenceUser(TicketRole $role): void
    {
        User::firstOrCreate(
            ['email' => $role->referenceEmail()],
            ['name' => Str::headline($role->value), 'password' => 'password'],
        )->syncRoles($role->value);
    }
}
