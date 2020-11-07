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
// Begin ProjectsController
$router->get('projects')->uses('ProjectsController@index')->name('admin.projects.index');
$router->get('projects/create')->uses('ProjectsController@create')->name('admin.projects.create');
$router->post('projects/create')->uses('ProjectsController@store')->name('admin.projects.store');
$router->get('projects/{projects}')->uses('ProjectsController@show')->name('admin.projects.show');
$router->get('projects/{projects}/edit')->uses('ProjectsController@edit')->name('admin.projects.edit');
$router->put('projects/{projects}')->uses('ProjectsController@update')->name('admin.projects.update');
$router->get('projects/{projects}/clone')->uses('ProjectsController@clone')->name('admin.projects.clone');
$router->get('projects/{projects}/explore')->uses('ProjectsController@explore')->name('admin.projects.explore');
$router->get('projects/{projects}/statistics')->uses('ProjectsController@statistics')->name('admin.projects.statistics');

$router->post('projects/sort')->uses('ProjectsController@sort')->name('admin.projects.sort');

$router->post('projects/{projects}/ocr')->uses('ProjectsController@ocr')->name('admin.projects.ocr');

$router->delete('projects/{projects}')->uses('ProjectsController@delete')->name('admin.projects.delete');
$router->delete('projects/{projects}/subject')->uses('ProjectsController@deleteSubjects')->name('admin.projects.deleteSubjects');
