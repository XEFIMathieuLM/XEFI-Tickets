<?php

return [
    'report' => [
        'examined' => ':count unresolved ticket(s) examined.',
        'escalated' => ':count ticket(s) moved up a priority.',
        'flagged' => ':count ticket(s) already critical and still overdue.',
    ],
    'mail' => [
        'subject' => ':count ticket(s) escalated',
        'greeting' => 'These tickets outran the target their priority carries.',
        'line' => '":title" is now :priority.',
    ],
];
