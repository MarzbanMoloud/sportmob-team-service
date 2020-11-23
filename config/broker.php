<?php

return [
    'kafka' => [
        'producer' => [
            'clientId' => env('BROKER_PRODUCER_CLIENT_ID', ''),
            'socketBlockingMaxMs' => env('BROKER_PRODUCER_SOCKET_BLOCKING_MAX_MS', 100),
            'messageTimeoutMs' => env('BROKER_PRODUCER_MESSAGE_TIMEOUT_MS', 300000),
            'pollTimeout' => env('BROKER_PRODUCER_POLL_TIMEOUT', 0)
        ],
        'consumer' => [
            'groupId' => env('BROKER_CONSUMER_GROUP_ID', ''),
            'offsetStoreMethod' => env('BROKER_CONSUMER_OFFSET_STORE_METHOD', 'broker'),
            'autoOffsetReset' => env('BROKER_CONSUMER_AUTO_OFFSET_RESET', 'smallest')
        ],
    ],
    'host' => env('BROKER_HOST', 'broker:9092'),
    'services' => [
        'player_name' => env('BROKER_SERVICE_PLAYER_NAME', ''),
        'team_name' => env('BROKER_SERVICE_TEAM_NAME', ''),
        'competition_name' => env('BROKER_SERVICE_COMPETITION_NAME', ''),
        'tournament_name' => env('BROKER_SERVICE_TOURNAMENT_NAME', ''),
        'coach_name' => env('BROKER_SERVICE_COACH_NAME', ''),
    ],
    'ask' => [
        'service' => [
            'response' => [
                'topic' => env('BROKER_ASK_SERVICE_RESPONSE_TOPIC', '')
            ],
            'request' => [
                'topic' => env('BROKER_ASK_SERVICE_REQUEST_TOPIC', '')
            ]
        ],
        'provider' => [
            'topic' => env('BROKER_ASK_PROVIDER_TOPIC', '')
        ]
    ]
];
