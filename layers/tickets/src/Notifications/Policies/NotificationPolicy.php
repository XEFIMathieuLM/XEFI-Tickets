<?php

namespace Tickets\Notifications\Policies;

/**
 * How loudly a ticket of a given priority speaks. One implementation per
 * behaviour, resolved from the priority, never chosen by the caller.
 */
interface NotificationPolicy
{
    /**
     * @return array<int, string>
     */
    public function channels(): array;
}
