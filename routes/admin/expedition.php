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
// Begin Expeditions Controller
$router->get('expeditions')->uses('ExpeditionsController@index')->name('admin.expeditions.index');
$router->post('expeditions/sort')->uses('ExpeditionsController@sort')->name('admin.expeditions.sort');
$router->get('projects/{projects}/expeditions/create')->uses('ExpeditionsController@create')->name('admin.expeditions.create');
$router->post('projects/{projects}/expeditions')->uses('ExpeditionsController@store')->name('admin.expeditions.store');
$router->get('projects/{projects}/expeditions/{expeditions}')->uses('ExpeditionsController@show')->name('admin.expeditions.show');


$router->get('projects/{projects}/expeditions/{expeditions}/edit')->uses('ExpeditionsController@edit')->name('admin.expeditions.edit');
$router->put('projects/{projects}/expeditions/{expeditions}')->uses('ExpeditionsController@update')->name('admin.expeditions.update');
$router->delete('projects/{projects}/expeditions/{expeditions}')->uses('ExpeditionsController@delete')->name('admin.expeditions.delete');

$router->get('projects/{projects}/expeditions/{expeditions}/clone')->uses('ExpeditionsController@clone')->name('admin.expeditions.clone');
$router->post('projects/{projects}/expeditions/{expeditions}/process')->uses('ExpeditionsController@process')->name('admin.expeditions.process');
$router->delete('projects/{projects}/expeditions/{expeditions}/stop')->uses('ExpeditionsController@stop')->name('admin.expeditions.stop');
$router->post('projects/{projects}/expeditions/{expeditions}/ocr')->uses('ExpeditionsController@ocr')->name('admin.expeditions.ocr');
