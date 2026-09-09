<?php

return [
    'heading' => 'Tickets',
    'heading_own' => 'My tickets',
    'filters' => [
        'status' => 'Status',
        'priority' => 'Priority',
        'any' => 'All',
    ],
    'columns' => [
        'title' => 'Title',
        'requester' => 'Requester',
        'technician' => 'Assigned to',
        'status' => 'Status',
        'priority' => 'Priority',
        'comments' => 'Comments',
        'created_at' => 'Opened on',
    ],
    'profiles' => [
        'requester' => 'opens tickets and sees its own',
        'technician' => 'sees the ones assigned to it',
        'manager' => 'sees everything, assigns and closes',
    ],
    'unassigned' => 'Nobody',
    'empty' => 'No ticket matches this view.',
];
