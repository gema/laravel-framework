<?php

return [
    'cache' => [
        'clear' => [
            'message' => 'Clear cache',
            // Locally it only clears
            'cleared' => 'Cache cleared',
            'optimized' => 'Cache cleared and optimized',
            'not_optimized' => 'Cache cleared, but optimizing failed: :error',
            'failed' => 'Clearing the cache failed: :error',
        ],
    ],

    'mail' => [
        'test' => [
            'message' => 'Send test mail',
            'prompt' => 'Send a test mail to:',
            'success' => 'Test mail sent to :email, with the :mailer mailer',
        ],
    ],

    'maintenance' => [
        'enable' => [
            'message' => 'Enable maintenance',
            'confirm' => 'Enable maintenance? The site becomes unavailable for everyone except this browser, which keeps access for 12 hours.',
            'success' => 'Maintenance enabled',
        ],
        'disable' => [
            'message' => 'Disable maintenance',
            'success' => 'Maintenance disabled',
        ],
    ],

    'build' => [
        'message' => 'Build',
        'success' => 'Code builded',
    ],
];
