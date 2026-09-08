<?php

use Illuminate\Support\Facades\Route;
use Tickets\Livewire\TicketForm;

Route::middleware('auth')->prefix('tickets')->group(function (): void {
    Route::get('/create', TicketForm::class)->name('tickets.create');
    Route::get('/{ticket}/edit', TicketForm::class)->name('tickets.edit');
});
