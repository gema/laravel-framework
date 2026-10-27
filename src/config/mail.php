<?php

// Added to the app's mail config without publishing it: the app's own keys win
return [
    'mailers' => [
        // MAIL_MAILER=microsoft-graph sends through the Microsoft Graph API instead of SMTP.
        // The Entra ID app needs the Mail.Send application permission; by default it's the Azure login app.
        'microsoft-graph' => [
            'transport' => 'microsoft-graph',
            'tenant_id' => env('MICROSOFT_GRAPH_TENANT_ID') ?: env('AZURE_TENANT_ID'),
            'client_id' => env('MICROSOFT_GRAPH_CLIENT_ID') ?: env('AZURE_CLIENT_ID'),
            'client_secret' => env('MICROSOFT_GRAPH_CLIENT_SECRET') ?: env('AZURE_CLIENT_SECRET'),
            'sender' => env('MICROSOFT_GRAPH_SENDER'),
            'save_to_sent_items' => env('MICROSOFT_GRAPH_SAVE_TO_SENT_ITEMS', true),
        ],
    ],
];
