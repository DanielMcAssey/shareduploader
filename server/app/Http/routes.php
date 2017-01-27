<?php

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

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->group(['prefix' => 'api/v1', 'namespace' => 'V1'], function () use ($app) {

    $app->POST('/auth/login', 'AuthController@loginPost');

    $app->group(['middleware' => 'auth'], function () use ($app) {

        $app->POST('/auth/login', 'AuthController@loginPost');

    });

});
/**
 * Routes for resource file
 */
//$app->get('file', 'UploadedFilesController@all');
//$app->get('file/{id}', 'UploadedFilesController@get');
//$app->post('file', 'UploadedFilesController@add');
//$app->put('file/{id}', 'UploadedFilesController@put');
//$app->delete('file/{id}', 'UploadedFilesController@remove');

/**
 * Routes for resource user
 */
$app->get('user', 'UsersController@all');
$app->get('user/{id}', 'UsersController@get');
$app->post('user', 'UsersController@add');
$app->put('user/{id}', 'UsersController@put');
$app->delete('user/{id}', 'UsersController@remove');
