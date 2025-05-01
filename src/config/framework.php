<?php

return [
    'colors' => [
        ['#283240', '40, 50, 64'],
        ['#702f39', '112, 47, 57'],
    ],

    'auto_admin_domains' => [
        'gemadigital.com',
        'brightnow.app',
        'immera.io',
    ],

    'sidebar' => [
        'filemanager' => false,
        'backups' => false,
        'translations' => true,
        'pages' => false,
        'authentication' => true,
        'settings' => false,
        'logs' => true,
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
];
