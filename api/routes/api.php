<?php
/** @var \Laravel\Lumen\Routing\Router $router */
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['prefix' => 'admin', 'namespace' => 'Admin'], function ($router) {
	$router->group(['prefix' => '/teams'], function ($router) {
		$router->get('/{team}', ['as' => 'admin-teams-show', 'uses' => 'TeamController@show']);
		$router->put('/{team}', ['as' => 'admin-teams-update', 'uses' => 'TeamController@update']);
	});
	$router->group(['prefix' => '/transfers/players'], function ($router) {
		$router->get('/{player}', ['as' => 'admin-transfers-players-index', 'uses' => 'TransferController@index']);
		$router->put('/{transfer}', ['as' => 'admin-transfers-players-update', 'uses' => 'TransferController@update']);
	});
});

$router->group(['prefix' => '/{lang}/teams', 'namespace' => 'Api', 'middleware' => 'set.lang'], function ($router) {
	$router->group(['prefix' => '/transfers'], function ($router) {
		$router->get('/team/{team}[/{season}]', ['as' => 'teams.transfers.team.list', 'uses' => 'TransferController@listByTeam']);
		$router->get('/player/{player}', ['as' => 'teams.transfers.player.list', 'uses' => 'TransferController@listByPlayer']);
		$router->put('/{action}/{transfer}', ['as' => 'teams.transfers.action', 'uses' => 'TransferController@userActionTransfer']);
	});
	$router->group(['prefix' => '/trophies'], function ($router) {
		$router->get('/team/{team}', ['as' => 'teams.trophies.by.team', 'uses' => 'TrophyController@trophiesByTeam']);
		$router->get('/competition/{competition}', ['as' => 'teams.trophies.by.competition', 'uses' => 'TrophyController@trophiesByCompetition']);
	});
	$router->get('/overview/{team}', ['as' => 'teams.overview.by.team', 'uses' => 'TeamController@overview']);
	$router->get('/favorite/{team}', ['as' => 'teams.favorite.by.team', 'uses' => 'TeamController@favorite']);
});

