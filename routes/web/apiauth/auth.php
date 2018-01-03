<?php

$router->get('/')->name('api.get.index')->uses('ApiAuthController@index');

$router->get('login')->uses('ApiLoginController@showLoginForm')->name('api.get.login');
$router->post('login')->uses('ApiLoginController@login')->name('api.post.login');
$router->get('logout')->uses('ApiLoginController@logout')->name('api.get.logout');

// Begin PasswordController
$router->get('password/reset', 'ApiForgotPasswordController@showLinkRequestForm')->name('api.password.request');
$router->post('password/email', 'ApiForgotPasswordController@sendResetLinkEmail')->name('api.password.email');
$router->get('password/reset/{token}', 'ApiResetPasswordController@showResetForm')->name('api.password.reset');
$router->post('password/reset', 'ApiResetPasswordController@reset')->name('api.password.post');

// Begin RegistrationController
$router->get('register')->uses('ApiRegisterController@showRegistrationForm')->name('api.get.register');
$router->post('register')->uses('ApiRegisterController@register')->name('api.post.register');
$router->get('resend')->uses('ApiRegisterController@showResendActivationForm')->name('api.get.resend');
$router->post('resend')->uses('ApiRegisterController@postResendActivation')->name('api.post.resend');
$router->get('users/{id}/activate/{code}')->uses('ApiRegisterController@getActivate')->name('api.get.activate');