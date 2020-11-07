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
// Begin Reconcile Controller
$router->get('reconciles/{expeditions}')->uses('ReconcilesController@index')->name('admin.reconciles.index');
$router->get('reconciles/{expeditions}/create')->uses('ReconcilesController@create')->name('admin.reconciles.create');
$router->put('reconciles/{expeditions}')->uses('ReconcilesController@update')->name('admin.reconciles.update');
$router->post('reconciles/{projects}/publish/{expeditions}')->uses('ReconcilesController@publish')->name('admin.reconciles.publish');
