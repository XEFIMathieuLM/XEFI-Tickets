<?php

namespace Tickets\Enums;

use Tickets\Actions\CloseTicket;
use Tickets\Actions\ResolveTicket;
use Tickets\Actions\StartTicketWork;
use Tickets\Actions\UnassignTicket;
use Tickets\Contracts\MovesTicket;

/**
 * Where the support team may send a ticket, and for each destination the
 * permission that guards it and the action that carries its side effects.
 */
enum TicketTransition: string
{
    case Wait = 'open';
    case Work = 'in_progress';
    case Resolve = 'resolved';
    case Close = 'closed';

    public function status(): TicketStatus
    {
        return TicketStatus::from($this->value);
    }

    public function permission(): TicketPermission
    {
        return match ($this) {
            self::Close => TicketPermission::Close,
            default => TicketPermission::Handle,
        };
    }

    /**
     * @return class-string<MovesTicket>
     */
    public function action(): string
    {
        return match ($this) {
            self::Wait => UnassignTicket::class,
            self::Work => StartTicketWork::class,
            self::Resolve => ResolveTicket::class,
            self::Close => CloseTicket::class,
        };
    }

    /**
     * The destinations open to a ticket standing at this status.
     *
     * @return array<int, self>
     */
    public static function reachableFrom(TicketStatus $status): array
    {
        return array_map(
            fn (TicketStatus $target): self => self::from($target->value),
            $status->allowedTransitions(),
        );
    }
}
