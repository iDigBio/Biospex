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
$router->get('events')->uses('EventsController@index')->name('front.events.index');
$router->post('events/sort/')->uses('EventsController@sort')->name('front.events.sort');
$router->get('events/{event}')->uses('EventsController@read')->name('front.events.read');
$router->get('events/{uuid}/signup')->uses('EventsController@signup')->name('front.events.signup');
$router->post('events/{uuid}/join')->uses('EventsController@join')->name('front.events.join');