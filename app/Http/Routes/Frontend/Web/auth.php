<?php
// Begin AuthController
$router->get('/login', [
    'uses' => 'AuthController@getLogin',
    'as'   => 'auth.get.login'
]);

$router->post('/login', [
    'uses' => 'AuthController@postLogin',
    'as'   => 'auth.post.login'
]);

$router->get('/logout', [
    'uses' => 'AuthController@getLogout',
    'as'   => 'auth.get.logout'
]);

$router->get('register/{code?}', [
    'uses' => 'AuthController@getRegister',
    'as'   => 'auth.get.register'
]);

$router->post('register', [
    'uses' => 'AuthController@postRegister',
    'as'   => 'auth.post.register'
]);

$router->get('/users/{id}/activate/{code}', [
    'uses' => 'AuthController@getActivate',
    'as'   => 'auth.get.activate'
]);

$router->get('resend', [
    'uses' => 'AuthController@getResendActivation',
    'as'   => 'auth.get.resend'
]);

$router->post('resend', [
    'uses' => 'AuthController@postResendActivation',
    'as'   => 'auth.post.resend'
]);
// End AuthController
