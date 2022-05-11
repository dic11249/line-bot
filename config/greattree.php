<?php

return [
    'enums' => [
        'system' =>  [
            'is_useful' => 'GreatTree\Base\Enums\System\IsUseful',
            'is_show' => 'GreatTree\Base\Enums\System\IsShow',
            'parameter' => 'GreatTree\Base\Enums\System\Parameter',
        ]
    ],
    'system' => [
        'database' => [
            'connection' => env('DB_CONNECTION', 'mysql'),
        ]
    ],
    'storage' => [
        'model' => [
            'file' => \GreatTree\Base\Models\Entity\Storage\File::class,
        ],
        'database' => [
            'connection' => env('DB_CONNECTION', 'mysql'),
            'tables' => [
                'file_log' => 'greattree_file_log',
                'file' => 'greattree_file',
                'file_relationships' => 'greattree_file_relationships'
            ]
        ],
    ],
    'logger' => [
        'database' => [
            'connection' => env('DB_CONNECTION', 'mysql'),
            'tables' => [
                'request_logs' => 'greattree_request_logs',
                'exception_logs' => 'greattree_exception_logs'
            ]
        ],
    ],
];
