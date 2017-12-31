<?php

// Begin AuthController
$router->get('/login')->uses('LoginController@showLoginForm')->name('app.get.login');
$router->post('/login')->uses('LoginController@login')->name('app.post.login');
$router->get('/logout')->uses('LoginController@logout')->name('app.get.logout');

// Begin PasswordController
$router->get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('app.password.request');
$router->post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('app.password.email');
$router->get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
$router->post('password/reset', 'ResetPasswordController@reset');

// Begin RegistrationController
$router->get('/register/{code?}')->uses('RegisterController@showRegistrationForm')->name('app.get.register');
$router->post('/register')->uses('RegisterController@register')->name('app.post.register');
$router->get('/resend')->uses('RegisterController@showResendActivationForm')->name('app.get.resend');
$router->post('/resend')->uses('RegisterController@postResendActivation')->name('app.post.resend');
$router->get('/users/{id}/activate/{code}')->uses('RegisterController@getActivate')->name('app.get.activate');
