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

$router->group(['prefix' => '/{lang}/teams/transfers', 'namespace' => 'Api', 'middleware' => 'set.lang'], function ($router) {
	$router->get('/team/{team}[/{season}]', ['as' => 'teams.transfers.team.list', 'uses' => 'TransferController@listByTeam']);
	$router->get('/player/{player}', ['as' => 'teams.transfers.player.list', 'uses' => 'TransferController@listByPlayer']);
	$router->put('/{action}/{user}/{transfer}', ['as' => 'teams.transfers.action', 'uses' => 'TransferController@userActionTransfer']);
});


