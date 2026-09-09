<?php

return [
    'heading' => 'Tickets',
    'heading_own' => 'Mes tickets',
    'filters' => [
        'status' => 'Statut',
        'priority' => 'Priorité',
        'any' => 'Tous',
    ],
    'columns' => [
        'title' => 'Titre',
        'requester' => 'Demandeur',
        'technician' => 'Assigné à',
        'status' => 'Statut',
        'priority' => 'Priorité',
        'comments' => 'Commentaires',
        'created_at' => 'Ouvert le',
    ],
    'profiles' => [
        'requester' => 'ouvre des tickets et voit les siens',
        'technician' => 'voit ceux qui lui sont assignés',
        'manager' => 'voit tout, assigne et clôture',
    ],
    'unassigned' => 'Personne',
    'empty' => 'Aucun ticket ne correspond à cette vue.',
];
