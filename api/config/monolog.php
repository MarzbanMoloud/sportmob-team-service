<?php

return [
	'index' => env('MONOLOG_INDEX', 'sportmob'),
	'handler' => [
		'elasticSearch' => [
			'host' => env('ELASTIC_HOST', ''),
			'port' => env('ELASTIC_PORT', 443),
			'transport' => env('ELASTIC_TRANSPORT', 'https'),
			'username' => env('ELASTIC_USERNAME', ''),
			'password' => env('ELASTIC_PASSWORD', '')
		]
	]
];