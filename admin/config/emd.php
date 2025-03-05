<?php

return [
    'app_domain' => env('APP_DOMAIN', 'yourdomain.com'),
    'https' => (bool) env('FORCE_HTTPS', false),
    'sender_transport' => env('SENDER_TRANSPORT', '/etc/postfix/sender_transport'),
    'api_rate' => (int) env('API_REQUEST_RATE', 600),
    'mail' => [
        'should_queue' => (bool) env('MAIL_SHOULD_QUEUE', true),
        'queue_name' => env('MAIL_QUEUE_NAME', 'default'),
        'track_click' => (bool) env('MAIL_TRACK_CLICK', false),
    ],
    'policy' => [
        'server_name' => env('POLICY_SERVER_NAME', 'Access Policy Delegation'),
        'server_worker' => env('POLICY_SERVER_WORKER', 4),
        'daemonize' => env('POLICY_DAEMONIZE', false),
        'adapter' => env('POLICY_ADAPTER', App\Mail\Policy\Adapter\Workerman::class),
        'listen_host' => env('POLICY_LISTEN_HOST', '0.0.0.0'),
        'listen_port' => env('POLICY_LISTEN_PORT', 1403),
    ],
];
