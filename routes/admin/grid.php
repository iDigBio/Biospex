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
// Project/Grid
$router->get('grids/{projects}/explore', ['as' => 'admin.grids.explore', 'uses' => 'GridsController@explore']);
$router->post('grids/{projects}/delete', ['as' => 'admin.grids.delete', 'uses' => 'GridsController@delete']);
$router->post('grids/{projects}/export', ['as' => 'admin.grids.export', 'uses' => 'GridsController@export']);

$router->get('grids/{projects}/expeditions/create', ['as' => 'admin.grids.create', 'uses' => 'GridsController@expeditionsCreate']);
$router->get('grids/{projects}/expeditions/{expeditions}', ['as' => 'admin.grids.show', 'uses' => 'GridsController@expeditionsShow']);
$router->get('grids/{projects}/expeditions/{expeditions}/edit', ['as' => 'admin.grids.edit', 'uses' => 'GridsController@expeditionsEdit']);
$router->post('grids/{projects}/expeditions/{expeditions}/export', ['as' => 'admin.grids.expedition.export', 'uses' => 'GridsController@export']);

