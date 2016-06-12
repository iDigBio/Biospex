<?php

// Begin PasswordController
$router->get('password/email', [
    'uses' => 'PasswordController@getEmail',
    'as'   => 'password.get.email'
]);

$router->post('password/email', [
    'uses' => 'PasswordController@postEmail',
    'as'   => 'password.post.email'
]);

$router->get('password/reset/{token}', [
    'uses' => 'PasswordController@getReset',
    'as'   => 'password.get.reset'
]);

$router->post('password/reset', [
    'uses' => 'PasswordController@postReset',
    'as'   => 'password.post.reset'
]);
// End PasswordsController