<?php

return [
    'events' => [
		'team_was_created' => env('EVENT_TEAM_WAS_CREATED', 'TeamWasCreated'),
		'team_was_updated' => env('EVENT_TEAM_WAS_UPDATED', 'TeamWasUpdated'),
		'membership_was_updated' => env('EVENT_MEMBERSHIP_WAS_UPDATED', 'MembershipWasUpdated'),
		'team_became_runner_up' => env('EVENT_TEAM_BECAME_RUNNER_UP', 'TeamBecameRunnerUp'),
		'team_became_winner' => env('EVENT_TEAM_BECAME_WINNER', 'TeamBecameWinner'),
		'match_was_created' => env('EVENT_MATCH_WAS_CREATED', 'MatchWasCreated'),
		'match_finished' => env('EVENT_MATCH_FINISHED', 'MatchFinished'),
		'match_status_changed' => env('EVENT_MATCH_STATUS_CHANGED', 'MatchStatusChanged'),
	]
];
