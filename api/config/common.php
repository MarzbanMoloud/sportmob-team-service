<?php

return [
    'limit' => [
    	'team_overview_upcoming' => env('LIMIT_TEAM_OVERVIEW_UPCOMING', 1),
    	'team_overview_finished' => env('LIMIT_TEAM_OVERVIEW_FINISHED', 5),
	],
    'error_codes' =>[
        'validation_failed' => 'TM-400',
        'resource_not_found' => 'TM-404',
        'unprocessable_request' => 'TM-422',
		'User_is_not_allowed_to_like' => 'TM-002',
		'team_update_failed' => 'TM-003',
		'transfer_update_failed' => 'TM-004',
    ]
];
