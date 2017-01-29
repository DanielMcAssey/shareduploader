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

$app->group(['prefix' => 'api/v1', 'namespace' => 'App\Http\Controllers\V1'], function () use ($app) {

    $app->post('auth/login', 'AuthController@login');
    $app->get('auth/refresh', 'AuthController@refresh');

    $app->group(['prefix' => 'api/v1', 'namespace' => 'App\Http\Controllers\V1', 'middleware' => 'auth'], function () use ($app) {

        $app->post('file', 'UploadedFilesController@upload');
        $app->delete('file/{id}', 'UploadedFilesController@delete');

        $app->get('user/current', 'UsersController@getCurrentUser');
        $app->get('user/api-key/generate', 'UsersController@generateApiKey');
        $app->get('user/api-key', 'UsersController@getApiKey');
        $app->post('user/password', 'UsersController@changePassword');
        $app->post('user/email/change', 'UsersController@confirmEmailChange');
        $app->post('user/email/confirm', 'UsersController@confirmEmailChange');
    });

});
