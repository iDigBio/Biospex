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
// Begin UsersController
$router->get('users')->uses('UsersController@index')->name('admin.users.index');
$router->get('users/{users}')->uses('UsersController@show')->name('admin.users.show');
$router->get('users/{users}/edit')->uses('UsersController@edit')->name('admin.users.edit');
$router->put('users/{users}')->uses('UsersController@update')->name('admin.users.update');
$router->put('password/{id}/pass')->uses('UsersController@pass')->name('admin.users.password');