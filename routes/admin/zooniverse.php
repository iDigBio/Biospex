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

use App\Http\Controllers\Admin\ZooniverseController;

Route::post('projects/{projects}/expeditions/{expeditions}/process', [ZooniverseController::class, 'process'])->name('admin.zooniverse.process');
Route::delete('projects/{projects}/expeditions/{expeditions}/stop', [ZooniverseController::class, 'stop'])->name('admin.zooniverse.stop');
Route::get('projects/{projects}/expeditions/{expeditions}/workflow', [ZooniverseController::class, 'workflowShowForm'])->name('admin.zooniverse.workflowShowForm');
Route::post('projects/{projects}/expeditions/{expeditions}/workflow', [ZooniverseController::class, 'workflowUpdateForm'])->name('admin.zooniverse.workflowUpdateForm');
