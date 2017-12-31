<?php

// forPersonalAccessTokens
$router->group(['middleware' => ['web', 'auth:apiuser']], function ($router) {
    $router->get('/scopes', [
        'uses' => 'ScopeController@all',
    ]);

    $router->get('/personal-access-tokens', [
        'uses' => 'PersonalAccessTokenController@forUser',
    ]);

    $router->post('/personal-access-tokens', [
        'uses' => 'PersonalAccessTokenController@store',
    ]);

    $router->delete('/personal-access-tokens/{token_id}', [
        'uses' => 'PersonalAccessTokenController@destroy',
    ]);
});