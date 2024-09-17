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
use App\Http\Controllers\Admin\GridController;
use App\Http\Controllers\Admin\ProjectGridExportController;

Route::get('grids/{projects}/explore', [GridController::class, 'explore'])->name('admin.grids.explore');
Route::post('grids/{projects}/delete', [GridController::class, 'delete'])->name('admin.grids.delete');

// Expeditions grid.
Route::get('grids/{project}/create', [ExpeditionGridController::class, 'create'])->name('admin.grids.create');
Route::get('grids/{expedition}', [ExpeditionGridController::class, 'show'])->name('admin.grids.show');
Route::get('grids/{expedition}/edit', [ExpeditionGridController::class, 'edit'])->name('admin.grids.edit');

// Export csv from grid button.
Route::post('grids/{expedition}/export', ExpeditionGridExportController::class)->name('admin.grids.expedition.export');
Route::post('grids/{projects}/export', ProjectGridExportController::class)->name('admin.grids.export');
