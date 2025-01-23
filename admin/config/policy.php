<?php

return [
    'server_name' => env('POLICY_SERVER_NAME', 'Access Policy Delegation'),
    'server_worker' => env('POLICY_SERVER_WORKER', 4),
    'daemonize' => env('POLICY_DAEMONIZE', false),
    'adapter' => env('POLICY_ADAPTER', App\Mail\Policy\Adapter\Workerman::class),
    'listen_host' => env('POLICY_LISTEN_HOST', '0.0.0.0'),
    'listen_port' => env('POLICY_LISTEN_PORT', 54321),
];
