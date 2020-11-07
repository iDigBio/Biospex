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
// Begin GroupsController
$router->get('groups')->uses('GroupsController@index')->name('admin.groups.index');
$router->get('groups/create')->uses('GroupsController@create')->name('admin.groups.create');
$router->post('groups')->uses('GroupsController@store')->name('admin.groups.store');
$router->get('groups/{groups}')->uses('GroupsController@show')->name('admin.groups.show');
$router->get('groups/{groups}/edit')->uses('GroupsController@edit')->name('admin.groups.edit');
$router->put('groups/{groups}')->uses('GroupsController@update')->name('admin.groups.update');
$router->delete('groups/{groups}')->uses('GroupsController@delete')->name('admin.groups.delete');
$router->delete('groups/{groups}/{user}')->uses('GroupsController@deleteUser')->name('admin.groups.deleteUser');
