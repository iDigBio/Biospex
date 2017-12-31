<?php

//forAuthorization
$router->group(['middleware' => ['web', 'auth:apiuser']], function ($router) {
    $router->get('/authorize', [
        'uses' => 'AuthorizationController@authorize',
    ]);

    $router->post('/authorize', [
        'uses' => 'ApproveAuthorizationController@approve',
    ]);

    $router->delete('/authorize', [
        'uses' => 'DenyAuthorizationController@deny',
    ]);
});