<?php

return [
    'heading' => 'Archive',
    'subheading' => 'Les tickets clôturés. Leur statut ne change plus.',
    'columns' => [
        'title' => 'Titre',
        'priority' => 'Priorité',
        'created_at' => 'Ouvert le',
        'resolved_at' => 'Résolu le',
        'requester' => 'Demandeur',
        'technician' => 'Traité par',
        'on_time' => 'Délai',
    ],
    'on_time' => [
        'met' => 'Tenu',
        'missed' => 'Dépassé',
        'unknown' => 'Non mesuré',
    ],
    'never_resolved' => 'Jamais résolu',
    'unassigned' => 'Personne',
    'empty' => 'Aucun ticket clôturé pour le moment.',
];
