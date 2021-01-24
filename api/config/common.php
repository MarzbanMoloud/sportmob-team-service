<?php

return [
    'limit' => [
    	'team_overview_upcoming' => env('LIMIT_TEAM_OVERVIEW_UPCOMING', 1),
    	'team_overview_finished' => env('LIMIT_TEAM_OVERVIEW_FINISHED', 5),
	],
    'error_codes' =>[
        'validation_failed' => 'TM-400',
        'resource_not_found' => 'TM-404',
        'Unprocessable_request' => 'TM-422',
		'transfer_team_seasons_not_found' => 'TM-001',
		'User_is_not_allowed_to_like' => 'TM-002',
		'team_update_failed' => 'TM-003',
    ]
];
