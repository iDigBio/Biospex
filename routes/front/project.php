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
$router->get('projects')->uses('ProjectsController@index')->name('front.projects.index');
$router->post('projects/sort')->uses('ProjectsController@sort')->name('front.projects.sort');
// Redirect old links to new
$router->get('project/{slug}', function($slug) {
    return redirect("/projects/$slug", 301);
});
$router->get('projects/{slug}')->uses('ProjectsController@project')->name('front.projects.slug');

// Project Transcriptions
$router->get('projects/{project}/transcriptions/{year}')->uses('TranscriptionsController@index')->name('front.projects.transcriptions');

// Project Map
$router->get('projects/{project}/{state?}')->uses('ProjectsController@state')->name('front.projects.state');

