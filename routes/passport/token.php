<?php

//forAccessTokens
$router->post('/token', [
    'uses' => 'AccessTokenController@issueToken',
    'middleware' => 'throttle',
]);

$router->group(['middleware' => ['web', 'auth:apiuser']], function ($router) {
    $router->get('/tokens', [
        'uses' => 'AuthorizedAccessTokenController@forUser',
    ]);

    $router->delete('/tokens/{token_id}', [
        'uses' => 'AuthorizedAccessTokenController@destroy',
    ]);
});

// forTransientTokens
$router->post('/token/refresh', [
    'middleware' => ['web', 'auth:apiuser'],
    'uses' => 'TransientTokenController@refresh',
]);