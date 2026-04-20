<?php

return [
    'colors' => [
        ['#283240', '40, 50, 64'],
        ['#702f39', '112, 47, 57'],
    ],

    'auto_admin_domains' => explode(',',
        env('AUTO_ADMIN_DOMAINS', 'gema.pt,immera.io')
    ),

    'registration_open' => env('REGISTRATION_OPEN', env('BACKPACK_REGISTRATION_OPEN', env('APP_ENV') === 'local')),

    'sidebar' => [
        'filemanager' => false,
        'backups' => false,
        'translations' => true,
        'pages' => false,
        'authentication' => true,
        'settings' => false,
        'logs' => true,
    ],

    'jsonExports' => [
        'path' => storage_path('app/public/data'),
        'webhook' => env('JSON_EXPORT_WEBHOOK_URL', false),
    ],

    'routes' => [
        'prefix' => 'admin',
        'middleware' => 'admin',
        'list' => [
            'dashboard' => 'backpack.dashboard',
            'login' => 'backpack.auth.login',
        ],
    ],

    'build' => [
        'enabled' => false,
        'path' => base_path('parcel/data.json'),
        'classes' => [
            // \App\Models\Article::class => \App\Http\Resources\Article::class,
            // \App\Models\Category::class => \App\Http\Resources\Category::class,
            // \App\Models\Page::class => \App\Http\Resources\Page::class,
        ],
    ],

    'alerts' => [
        'ignore' => [
            'SQLSTATE[HY000] [2002] Connection refused',
        ],
    ],
];
