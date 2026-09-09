<?php

/*
 * Partial on purpose: anything missing falls back to the English file.
 */
return [
    'required' => 'Le champ :attribute est obligatoire.',
    'email' => 'Le champ :attribute doit être une adresse e-mail valide.',
    'string' => 'Le champ :attribute doit être une chaîne de caractères.',
    'date' => 'Le champ :attribute doit être une date valide.',
    'file' => 'Le champ :attribute doit être un fichier.',
    'enum' => 'La valeur du champ :attribute est invalide.',
    'prohibited' => 'Le champ :attribute est interdit.',
    'extensions' => 'Le champ :attribute doit avoir une extension parmi : :values.',
    'max' => [
        'file' => 'Le champ :attribute ne doit pas dépasser :max kilo-octets.',
        'string' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
    ],
];
