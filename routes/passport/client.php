<?php

// forClients
$router->group(['middleware' => ['web', 'auth:apiuser']], function ($router) {
    $router->get('/clients', [
        'uses' => 'ClientController@forUser',
    ]);

    $router->post('/clients', [
        'uses' => 'ClientController@store',
    ]);

    $router->put('/clients/{client_id}', [
        'uses' => 'ClientController@update',
    ]);

    $router->delete('/clients/{client_id}', [
        'uses' => 'ClientController@destroy',
    ]);
});