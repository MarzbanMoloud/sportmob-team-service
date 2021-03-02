<?php

return [
    "account"  => env( "AWS_ACCOUNT_ID" ),
    "dynamoDb" => [
        'credentials' => [
            'key'    => env( 'AWS_ACCESS_KEY_ID' ),
            'secret' => env( 'AWS_SECRET_ACCESS_KEY' ),
        ],
        'region'      => env( 'AWS_REGION' ),
        'version'     => 'latest',
        'endpoint'    => env( 'AWS_DYNAMODB_ENDPOINT' ),
    ],
    'sns'      => [
        'region'      => env( 'AWS_REGION'  ),
        'version'     => env( 'AWS_VERSION', 'latest' ),
        "credentials" => [
            "key"    => env( 'AWS_ACCESS_KEY_ID'  ),
            "secret" => env( 'AWS_SECRET_ACCESS_KEY' )
        ],
    ],
    'sqs'      => [
        'region'      => env( 'AWS_REGION' ),
        'version'     => env( 'AWS_VERSION', 'latest' ),
        "credentials" => [
            "key"    => env( 'AWS_ACCESS_KEY_ID' ),
            "secret" => env( 'AWS_SECRET_ACCESS_KEY' )
        ],
    ]
];