<?php

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


// Register email
$router->get('email/verify', 'ApiVerificationController@show')->name('api.verification.notice');
$router->get('email/verify/{id}/{hash}', 'ApiVerificationController@verify')->name('api.verification.verify');
$router->get('email/resend', 'ApiVerificationController@resend')->name('api.verification.resend');

/*
$router->get('resend')->uses('ApiRegisterController@showResendActivationForm')->name('api.get.resend');
$router->post('resend')->uses('ApiRegisterController@postResendActivation')->name('api.post.resend');
$router->get('users/{id}/activate/{code}')->uses('ApiRegisterController@getActivate')->name('api.get.activate');
*/