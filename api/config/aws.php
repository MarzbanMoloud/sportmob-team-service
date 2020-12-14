<?php

return [
    "dynamoDb" => [
        'credentials' => [
            'key' => env('AWS_ACCESS_KEY_ID', 'local_dynamodb'),
            'secret' => env('AWS_SECRET_ACCESS_KEY', 'local_dynamodb'),
        ],
        'region' => env('AWS_REGION', 'us-west-1'),
        'version' => 'latest',
        'endpoint' => env('AWS_DYNAMODB_ENDPOINT', 'http://dynamodb:8000'),

        // You can override settings for specific services
        'Ses' => [
            'region' => 'us-west-1',
        ],
    ],
    'sns' => [
        'region' => env('AWS_SNS_DEFAULT_REGION', 'us-west-2'),
        'version' => env('AWS_SNS_VERSION', 'latest'),
        'debug' => env('AWS_SNS_DEBUG', false),
        'endpoint' => env('AWS_SNS_ENDPOINT', 'http://localstack:4566'),
        "credentials" => [
            "key" => env('AWS_SNS_ACCESS_KEY_ID', 'local'),
            "secret" => env('AWS_SNS_SECRET_ACCESS_KEY', 'local')
        ]
    ],
    'sqs' => [
        'region' => env('AWS_SQS_DEFAULT_REGION', 'us-west-2'),
        'version' => env('AWS_SQS_VERSION', 'latest'),
        'debug' => env('AWS_SQS_DEBUG', false),
        'endpoint' => env('AWS_SQS_ENDPOINT', 'http://localstack:4566'),
        "credentials" => [
            "key" => env('AWS_SQS_ACCESS_KEY_ID', 'local'),
            "secret" => env('AWS_SQS_SECRET_ACCESS_KEY', 'local')
        ]
    ]
];