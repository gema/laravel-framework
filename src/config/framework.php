<?php

return [
    'colors' => [
        ['#283240', 'rgb(40, 50, 64)'],
        ['#702f39', 'rgb(112, 47, 57)'],
    ],

    'sidebar' => [
        'filemanager' => true,
        'backups' => true,
        'translations' => true,
        'pages' => true,
        'authentication' => true,
        'settings' => true,
        'logs' => true,
    ],

    'build' => [
        'enabled' => true,
        'path' => base_path('parcel/data.json'),
        'classes' => [
            // \App\Models\Article::class => \App\Http\Resources\Article::class,
            // \App\Models\Category::class => \App\Http\Resources\Category::class,
            // \App\Models\Page::class => \App\Http\Resources\Page::class,
        ],
    ],
];
