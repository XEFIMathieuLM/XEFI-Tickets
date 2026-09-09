<?php

namespace Tickets\Enums;

/**
 * Groups the permissions, for the seeder and the tests only: no authorization
 * path reads a role name.
 */
enum TicketRole: string
{
    case Requester = 'requester';
    case Technician = 'technician';
    case Manager = 'manager';

    /**
     * The key a view translates. The wording itself never enters the enum.
     */
    public function translationKey(): string
    {
        return "tickets::role.{$this->value}";
    }

    /**
     * The address of the reference account the seeder creates for this profile.
     */
    public function referenceEmail(): string
    {
        return "{$this->value}@xefi.test";
    }

    /**
     * @return array<int, TicketPermission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Requester => [
                TicketPermission::ViewOwn,
                TicketPermission::Create,
                TicketPermission::Close,
            ],
            self::Technician => [
                TicketPermission::ViewAssigned,
                TicketPermission::Handle,
                TicketPermission::Close,
            ],
            self::Manager => [
                TicketPermission::ViewAll,
                TicketPermission::Assign,
                TicketPermission::Handle,
                TicketPermission::Close,
            ],
        };
    }
}
