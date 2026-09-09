<?php

return [
    'report' => [
        'examined' => ':count ticket(s) non résolu(s) examiné(s).',
        'escalated' => ':count ticket(s) passé(s) à la priorité supérieure.',
        'flagged' => ':count ticket(s) déjà critique(s) et toujours en retard.',
    ],
    'mail' => [
        'subject' => ':count ticket(s) escaladé(s)',
        'greeting' => 'Ces tickets ont dépassé le délai porté par leur priorité.',
        'line' => '« :title » est désormais en priorité :priority.',
    ],
];
