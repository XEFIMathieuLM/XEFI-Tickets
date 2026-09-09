<?php

return [
    'heading' => 'Pièces jointes',
    'fields' => [
        'upload' => 'fichier',
    ],
    'labels' => [
        'upload' => 'Ajouter un fichier',
    ],
    'hints' => [
        'max_size' => ':size Ko maximum par fichier.',
        'optional_at_opening' => 'Facultatif. :size Ko maximum. Vous pourrez en ajouter d’autres ensuite.',
    ],
    'actions' => [
        'attach' => 'Joindre',
        'remove' => 'Supprimer le fichier :name',
    ],
    'feedback' => [
        'attached' => 'Le fichier a été joint.',
        'removed' => 'Le fichier a été supprimé.',
    ],
    'empty' => 'Aucun fichier n’est encore joint à ce ticket.',
];
