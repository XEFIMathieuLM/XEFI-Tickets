<?php

namespace Tickets\Enums;

/**
 * The three access profiles, and the permissions each one groups.
 *
 * This enum exists to seed the roles and to set up tests. Nothing in the
 * runtime authorization path reads it: perimeters and policies only ever
 * check a TicketPermission.
 */
enum TicketRole: string
{
    case Requester = 'requester';
    case Technician = 'technician';
    case Manager = 'manager';

    /**
     * The address of the reference account the seeder creates for this profile.
     */
    public function referenceEmail(): string
    {
        return $this->value.'@xefi.test';
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
            ],
            self::Technician => [
                TicketPermission::ViewAssigned,
            ],
            self::Manager => [
                TicketPermission::ViewAll,
                TicketPermission::Assign,
                TicketPermission::Close,
            ],
        };
    }
}
