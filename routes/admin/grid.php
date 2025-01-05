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

use App\Http\Controllers\Admin\ExpeditionGridController;
use App\Http\Controllers\Admin\ExpeditionGridExportController;
use App\Http\Controllers\Admin\ProjectGridController;
use App\Http\Controllers\Admin\ProjectGridExportController;

Route::get('grids/{project}/explore', ProjectGridController::class)->name('admin.grids.project.index');

// Expeditions grid.
Route::get('grids/{project}/create', [ExpeditionGridController::class, 'create'])->name('admin.grids.expeditions.create');
Route::get('grids/{expedition}', [ExpeditionGridController::class, 'show'])->name('admin.grids.expeditions.show');
Route::get('grids/{expedition}/edit', [ExpeditionGridController::class, 'edit'])->name('admin.grids.expeditions.edit');

// Export csv from grid button.
Route::post('grids/{expedition}/expedition-export', ExpeditionGridExportController::class)->name('admin.grids.expeditions.export');
Route::post('grids/{project}/project-export', ProjectGridExportController::class)->name('admin.grids.projects.export');
