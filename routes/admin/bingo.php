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
// Begin BingosController
$router->get('bingos')->uses('BingosController@index')->name('admin.bingos.index');
$router->post('bingos/sort/')->uses('BingosController@sort')->name('admin.bingos.sort');
$router->get('bingos/create')->uses('BingosController@create')->name('admin.bingos.create');
$router->post('bingos/create')->uses('BingosController@store')->name('admin.bingos.store');
$router->get('bingos/{bingos}')->uses('BingosController@show')->name('admin.bingos.show');
$router->get('bingos/{bingos}/edit')->uses('BingosController@edit')->name('admin.bingos.edit');
$router->put('bingos/{bingos}')->uses('BingosController@update')->name('admin.bingos.update');
$router->delete('bingos/{bingos}')->uses('BingosController@delete')->name('admin.bingos.delete');

