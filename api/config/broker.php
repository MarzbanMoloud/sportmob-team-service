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
        'event_team_was_created' => env( 'BROKER_TOPIC_TEAM_WAS_CREATED', 'team_was_created_topic' ),
        'event_team_was_updated' => env( 'BROKER_TOPIC_TEAM_WAS_UPDATED', 'team_was_updated_topic' ),
        'event_membership_was_updated' => env( 'BROKER_TOPIC_MEMBERSHIP_WAS_UPDATED', 'membership_was_updated_topic' ),
        'event_team_became_runner_up' => env( 'BROKER_TOPIC_TEAM_BECAME_RUNNER_UP', 'team_became_runner_up_topic' ),
        'event_team_became_winner' => env( 'BROKER_TOPIC_TEAM_BECAME_WINNER', 'team_became_winner_topic' ),
        'event_match_was_created' => env( 'BROKER_TOPIC_MATCH_WAS_CREATED', 'match_was_created_topic' ),
        'event_match_finished' => env( 'BROKER_TOPIC_MATCH_FINISHED', 'match_finished_topic' ),
        'event_match_status_changed' => env( 'BROKER_TOPIC_MATCH_STATUS_CHANGED', 'match_status_changed_topic' ),
		'question_player'     => env( 'BROKER_TOPIC_PLAYER_QUESTION', 'player_question_topic' ),
		'question_competition'     => env( 'BROKER_TOPIC_COMPETITION_QUESTION', 'competition_question_topic' ),
		'question_team'     => env( 'BROKER_TOPIC_TEAM_QUESTION', 'team_question_topic' ),
		'answer_team'       => env( 'BROKER_TOPIC_TEAM_ANSWER', 'team_answer_topic' ),
		'answer_match'       => env( 'BROKER_TOPIC_MATCH_ANSWER', 'match_answer_topic' ),
		'answer_competition'       => env( 'BROKER_TOPIC_COMPETITION_ANSWER', 'competition_answer_topic' ),
		'answer_player'       => env( 'BROKER_TOPIC_PLAYER_ANSWER', 'player_answer_topic' ),
        'notification' => env( 'BROKER_TOPIC_NOTIFICATION', 'notification_topic' ),
    ],
    'queues'      => [
        'event'        => env( 'BROKER_QUEUE_EVENT', 'service_event_queue' ),
        'question'     => env( 'BROKER_QUEUE_QUESTION', 'service_question_queue' ),
        'answer'       => env( 'BROKER_QUEUE_ANSWER', 'service_answer_queue' ),
        'notification' => env( 'BROKER_QUEUE_NOTIFICATION', 'service_notification_queue' ),
    ]
];
