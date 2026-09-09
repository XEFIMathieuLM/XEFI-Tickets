<?php

return [
    'heading' => 'Attachments',
    'fields' => [
        'upload' => 'file',
    ],
    'labels' => [
        'upload' => 'Add a file',
    ],
    'hints' => [
        'max_size' => 'Up to :size KB per file.',
        'optional_at_opening' => 'Optional. Up to :size KB. You may add more afterwards.',
    ],
    'actions' => [
        'attach' => 'Attach',
        'remove' => 'Remove the file :name',
    ],
    'feedback' => [
        'attached' => 'The file has been attached.',
        'removed' => 'The file has been removed.',
    ],
    'empty' => 'No file is attached to this ticket yet.',
];
