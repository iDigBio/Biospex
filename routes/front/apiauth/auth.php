<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

Route::get('login')->uses('ApiLoginController@showLoginForm')->name('api.get.login');
Route::post('login')->uses('ApiLoginController@login')->name('api.post.login');
Route::get('logout')->uses('ApiLoginController@logout')->name('api.get.logout');

// Begin PasswordController
Route::get('password/reset', 'ApiForgotPasswordController@showLinkRequestForm')->name('api.password.request');
Route::post('password/email', 'ApiForgotPasswordController@sendResetLinkEmail')->name('api.password.email');
Route::get('password/reset/{token}', 'ApiResetPasswordController@showResetForm')->name('api.password.reset');
Route::post('password/reset', 'ApiResetPasswordController@reset')->name('api.password.post');

// Begin RegistrationController
Route::get('register')->uses('ApiRegisterController@showRegistrationForm')->name('api.get.register');
Route::post('register')->uses('ApiRegisterController@register')->name('api.post.register');


// Register email
Route::get('email/verify', 'ApiVerificationController@show')->name('api.verification.notice');
Route::get('email/verify/{id}/{hash}', 'ApiVerificationController@verify')->name('api.verification.verify');
Route::get('email/resend', 'ApiVerificationController@resend')->name('api.verification.resend');
