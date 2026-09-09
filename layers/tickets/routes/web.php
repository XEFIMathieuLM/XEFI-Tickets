<?php

use Illuminate\Support\Facades\Route;
use Tickets\Livewire\TicketArchive;
use Tickets\Livewire\TicketForm;
use Tickets\Livewire\TicketList;

Route::middleware('auth')->prefix('tickets')->group(function (): void {
    Route::get('/', TicketList::class)->name('tickets.index');
    Route::get('/archive', TicketArchive::class)->name('tickets.archive');
    Route::get('/create', TicketForm::class)->name('tickets.create');
    Route::get('/{ticket}/edit', TicketForm::class)->name('tickets.edit');
});
