<?php

return [
    'heading' => [
        'create' => 'Open a ticket',
        'edit' => 'Edit the ticket',
    ],
    'fields' => [
        'title' => 'title',
        'description' => 'description',
        'priority' => 'priority',
    ],
    'labels' => [
        'title' => 'Title',
        'description' => 'Description',
        'priority' => 'Priority',
        'current_status' => 'Current status',
        'assign_to' => 'Assign to',
    ],
    'actions' => [
        'save' => 'Save',
        'assign' => 'Assign this ticket to :name',
    ],
    'feedback' => [
        'opened' => 'The ticket has been opened.',
        'updated' => 'The ticket has been saved.',
        'assigned' => 'The ticket has been assigned.',
        'transition_refused' => 'A ticket cannot move from ":from" to ":target".',
        'ticket_is_closed' => 'This ticket is closed: it can no longer be changed.',
    ],
    'empty' => [
        'technicians' => 'No technician can take this ticket yet.',
    ],
];
