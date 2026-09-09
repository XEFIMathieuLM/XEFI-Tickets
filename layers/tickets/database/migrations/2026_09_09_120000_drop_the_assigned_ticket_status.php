<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Tickets\Enums\TicketStatus;

/**
 * Holding a ticket is no longer a step of the lifecycle, so the tickets that
 * stood at "assigned" go back to waiting. They keep their technician.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('tickets')
            ->where('status', 'assigned')
            ->update(['status' => TicketStatus::Open->value]);

        DB::table('ticket_change_logs')
            ->where('attribute', 'status')
            ->where('old_value', 'assigned')
            ->update(['old_value' => TicketStatus::Open->value]);

        DB::table('ticket_change_logs')
            ->where('attribute', 'status')
            ->where('new_value', 'assigned')
            ->update(['new_value' => TicketStatus::Open->value]);
    }
};
