<?php

use Illuminate\Support\Facades\Route;

// Authentication Routes...
Route::get('login', 'LoginController@showLoginForm')->name('app.get.login');
Route::post('login', 'LoginController@login')->name('app.post.login');
Route::get('logout', 'LoginController@logout')->name('app.get.logout');

// Reset password
Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('app.password.request');
Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('app.password.email');
Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'ResetPasswordController@reset')->name('password.update');

// Registration Routes...
Route::get('register/{code?}', 'RegisterController@showRegistrationForm')->name('app.get.register');
Route::post('register', 'RegisterController@register')->name('app.post.register');

// Register email
Route::get('email/verify', 'VerificationController@show')->name('verification.notice');
Route::get('email/verify/{id}/{hash}', 'VerificationController@verify')->name('verification.verify');
Route::get('email/resend', 'VerificationController@resend')->name('verification.resend');