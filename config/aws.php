<?php

return [
    'credentials' => [
        'key'    => env('AWS_ACCESS_KEY_ID', 'local_dynamodb'),
        'secret' => env('AWS_SECRET_ACCESS_KEY', 'local_dynamodb'),
    ],
    'region' => env('AWS_REGION', 'us-west-1'),
    'version' => env('AWS_VERSION', 'latest'),
	'endpoint' => env('AWS_ENDPOINT', 'http://dynamodb:8000'),

    // You can override settings for specific services
    'Ses' => [
        'region' => 'us-west-1',
    ],
];
