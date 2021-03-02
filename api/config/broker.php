<?php

return [
    'host' => env('BROKER_HOST', 'broker:9092'),
    'services' => [
        'player_name' => env('BROKER_SERVICE_PLAYER_NAME', 'player'),
        'team_name' => env('BROKER_SERVICE_TEAM_NAME', 'team'),
        'competition_name' => env('BROKER_SERVICE_COMPETITION_NAME', 'competition'),
        'tournament_name' => env('BROKER_SERVICE_TOURNAMENT_NAME', 'tournament'),
        'coach_name' => env('BROKER_SERVICE_COACH_NAME', 'coach'),
    ],
    'visibility_timeout_message' => env('VISIBILITY_TIMEOUT_MESSAGE', 20),
    'topics'      => [
        'event'        => env( 'BROKER_TOPIC_EVENT', 'event_topic' ),
        'question'     => env( 'BROKER_TOPIC_QUESTION', 'question_topic' ),
        'answer'       => env( 'BROKER_TOPIC_ANSWER', 'answer_topic' ),
        'notification' => env( 'BROKER_TOPIC_NOTIFICATION', 'notification_topic' ),
    ],
    'queues'      => [
        'event'        => env( 'BROKER_QUEUE_EVENT', 'service_event_queue' ),
        'question'     => env( 'BROKER_QUEUE_QUESTION', 'service_question_queue' ),
        'answer'       => env( 'BROKER_QUEUE_ANSWER', 'service_answer_queue' ),
        'notification' => env( 'BROKER_QUEUE_NOTIFICATION', 'notification_queue' ),
    ]
];
