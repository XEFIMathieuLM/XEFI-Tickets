<?php

return [
    'heading' => [
        'create' => 'Ouvrir un ticket',
        'edit' => 'Modifier le ticket',
    ],
    'fields' => [
        'title' => 'titre',
        'description' => 'description',
        'priority' => 'priorité',
    ],
    'labels' => [
        'title' => 'Titre',
        'description' => 'Description',
        'priority' => 'Priorité',
        'current_status' => 'Statut actuel',
        'assign_to' => 'Assigner à',
    ],
    'actions' => [
        'save' => 'Enregistrer',
        'assign' => 'Assigner ce ticket à :name',
    ],
    'feedback' => [
        'opened' => 'Le ticket a été ouvert.',
        'updated' => 'Le ticket a été enregistré.',
        'assigned' => 'Le ticket a été assigné.',
        'transition_refused' => 'Un ticket ne peut pas passer de « :from » à « :target ».',
    ],
    'empty' => [
        'technicians' => 'Aucun technicien ne peut encore prendre ce ticket.',
    ],
];
