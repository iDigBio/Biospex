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
// Begin Public Project
Route::get('projects')->uses('ProjectController@index')->name('front.projects.index');
Route::post('projects/sort')->uses('ProjectController@sort')->name('front.projects.sort');
// Redirect old links to new
Route::get('project/{slug}', function($slug) {
    return redirect("/projects/$slug", 301);
});
Route::get('projects/{slug}')->uses('ProjectController@project')->name('front.projects.slug');

// Project Transcriptions
Route::get('projects/{project}/transcriptions/{year}')->uses('TranscriptionController@index')->name('front.projects.transcriptions');

// Project Map
Route::get('projects/{project}/{state?}')->uses('ProjectController@state')->name('front.projects.state');

