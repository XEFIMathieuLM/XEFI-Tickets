<?php

return [
    'heading' => 'Archive',
    'subheading' => 'The closed tickets. Their status no longer moves.',
    'columns' => [
        'title' => 'Title',
        'priority' => 'Priority',
        'created_at' => 'Opened on',
        'resolved_at' => 'Resolved on',
        'requester' => 'Requester',
        'technician' => 'Handled by',
        'on_time' => 'Target',
    ],
    'on_time' => [
        'met' => 'Met',
        'missed' => 'Missed',
        'unknown' => 'Not measured',
    ],
    'never_resolved' => 'Never resolved',
    'unassigned' => 'Nobody',
    'empty' => 'No ticket has been closed yet.',
];
