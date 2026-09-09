<?php

namespace Tickets\Http\Controllers;

use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tickets\Access\Controls\TicketControl;
use Tickets\Models\Ticket;

/**
 * Outside the REST resource on purpose: this produces a file in another
 * format, so there is nothing for a Resource to declare.
 */
class ExportResolvedTicketsController
{
    /**
     * @var array<int, string>
     */
    private const COLUMNS = ['id', 'title', 'status', 'priority', 'created_at', 'resolved_at'];

    public function __invoke(): StreamedResponse
    {
        $month = Carbon::now()->startOfMonth();

        $resolvedTickets = app(TicketControl::class)
            ->forCurrentUser(Ticket::query())
            ->select(self::COLUMNS)
            ->whereBetween('resolved_at', [$month, $month->copy()->endOfMonth()])
            ->orderBy('resolved_at');

        return response()->streamDownload(
            function () use ($resolvedTickets): void {
                $csv = new \SplFileObject('php://output', 'w');
                $csv->fputcsv(self::COLUMNS, escape: '');

                $resolvedTickets->lazyById()->each(function (Ticket $ticket) use ($csv): void {
                    $csv->fputcsv([
                        $ticket->id,
                        $ticket->title,
                        $ticket->status->value,
                        $ticket->priority->value,
                        $ticket->created_at?->toDateTimeString(),
                        $ticket->resolved_at?->toDateTimeString(),
                    ], escape: '');
                });
            },
            "resolved-tickets-{$month->format('Y-m')}.csv",
            ['Content-Type' => 'text/csv'],
        );
    }
}
