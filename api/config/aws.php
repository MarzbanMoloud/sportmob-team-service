<?php

return [
    "account"  => env( "AWS_ACCOUNT_ID", '123456789100' ),
    "dynamoDb" => [
        'credentials' => [
            'key'    => env( 'AWS_ACCESS_KEY_ID', 'local_dynamodb' ),
            'secret' => env( 'AWS_SECRET_ACCESS_KEY', 'local_dynamodb' ),
        ],
        'region'      => env( 'AWS_REGION', 'us-west-1' ),
        'version'     => 'latest',
        'endpoint'    => env( 'AWS_DYNAMODB_ENDPOINT', 'http://dynamodb:8000' ),
    ],
    'sns'      => [
        'region'      => env( 'AWS_REGION', 'us-west-2' ),
        'version'     => env( 'AWS_VERSION', 'latest' ),
        "credentials" => [
            "key"    => env( 'AWS_ACCESS_KEY_ID', 'local' ),
            "secret" => env( 'AWS_SECRET_ACCESS_KEY', 'local' )
        ],
    ],
    'sqs'      => [
        'region'      => env( 'AWS_REGION', 'us-west-2' ),
        'version'     => env( 'AWS_VERSION', 'latest' ),
        "credentials" => [
            "key"    => env( 'AWS_ACCESS_KEY_ID', 'local' ),
            "secret" => env( 'AWS_SECRET_ACCESS_KEY', 'local' )
        ],
    ]
];