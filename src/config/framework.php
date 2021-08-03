<?php

return [
    'colors' => [
        '#283240',
        '#702f39',
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
            \App\Models\Article::class => \App\Http\Resources\Article::class,
            \App\Models\Category::class => \App\Http\Resources\Category::class,
            \App\Models\Page::class => \App\Http\Resources\Page::class,
        ],
    ],
];
