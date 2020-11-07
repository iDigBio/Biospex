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
// Begin EventsController
$router->get('events')->uses('EventsController@index')->name('admin.events.index');
$router->post('events/sort/')->uses('EventsController@sort')->name('admin.events.sort');
$router->get('events/create')->uses('EventsController@create')->name('admin.events.create');
$router->post('events/create')->uses('EventsController@store')->name('admin.events.store');
$router->get('events/{events}')->uses('EventsController@show')->name('admin.events.show');
$router->get('events/{events}/edit')->uses('EventsController@edit')->name('admin.events.edit');
$router->put('events/{events}')->uses('EventsController@update')->name('admin.events.update');
$router->delete('events/{events}')->uses('EventsController@delete')->name('admin.events.delete');
$router->get('events/{events}/transcriptions')->uses('EventsController@exportTranscriptions')->name('admin.events.exportTranscriptions');
$router->get('events/{events}/users')->uses('EventsController@exportUsers')->name('admin.events.exportUsers');