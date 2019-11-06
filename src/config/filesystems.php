<?php

return [

    'disks' => [

        'uploads' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
            'url' => env('APP_URL') . '/uploads',
            'visibility' => 'public',
        ],

        'backups' => [
            'driver' => 'local',
            'root' => storage_path('backups'),
        ],

        'storage' => [
            'driver' => 'local',
            'root' => storage_path(),
        ],

    ],

];
