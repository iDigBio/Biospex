<?php
/*
// Begin AuthController
Route::get('/login')->uses('LoginController@showLoginForm')->name('app.get.login');
Route::post('/login')->uses('LoginController@login')->name('app.post.login');
Route::get('/logout')->uses('LoginController@logout')->name('app.get.logout');

// Begin PasswordController
Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('app.password.request');
Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('app.password.email');
Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'ResetPasswordController@reset');

// Begin RegistrationController
Route::get('/register/{code?}')->uses('RegisterController@showRegistrationForm')->name('app.get.register');
Route::post('/register')->uses('RegisterController@register')->name('app.post.register');
Route::get('/resend')->uses('RegisterController@showResendActivationForm')->name('app.get.resend');
Route::post('/resend')->uses('RegisterController@postResendActivation')->name('app.post.resend');
Route::get('/users/{id}/activate/{code}')->uses('RegisterController@getActivate')->name('app.get.activate');
*/

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;

Route::get('login', [LoginController::class, 'showLoginForm'])->name('app.get.login');
Route::post('login', [LoginController::class, 'login'])->name('app.post.login');
Route::get('logout', [LoginController::class, 'logout'])->name('app.get.logout');

// Reset password
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('app.password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('app.password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Registration Routes...
Route::get('register/{invite?}', [RegisterController::class, 'showRegistrationForm'])->name('app.get.register');
Route::post('register/{invite?}', [RegisterController::class, 'register'])->name('app.post.register');

// Register email
Route::get('email/verify', [VerificationController::class, 'show'])->name('verification.notice');
Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::get('email/resend', [VerificationController::class, 'resend'])->name('verification.resend');
