<?php

return [
    'events' => [
		'team_was_created' => env('EVENT_TEAM_WAS_CREATED', 'TeamWasCreated'),
		'player_was_transferred' => env('EVENT_PLAYER_WAS_TRANSFERRED', 'PlayerWasTransferred'),
		'team_became_runner_up' => env('EVENT_TEAM_BECAME_RUNNER_UP', 'TeamBecameRunnerUp'),
		'team_became_winner' => env('EVENT_TEAM_BECAME_WINNER', 'TeamBecameWinner'),
		'match_was_created' => env('EVENT_MATCH_WAS_CREATED', 'MatchWasCreated'),
		'match_finished' => env('EVENT_MATCH_FINISHED', 'MatchFinished'),
		'match_status_changed' => env('EVENT_MATCH_STATUS_CHANGED', 'MatchStatusChanged'),
	]
];
