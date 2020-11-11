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

use App\Http\Controllers\Admin\GridsController;

Route::get('grids/{projects}/explore', [GridsController::class, 'explore'])->name('admin.grids.explore');
Route::post('grids/{projects}/delete', [GridsController::class, 'delete'])->name('admin.grids.delete');
Route::post('grids/{projects}/export', [GridsController::class, 'export'])->name('admin.grids.export');

Route::get('grids/{projects}/expeditions/create', [GridsController::class, 'expeditionsCreate'])->name('admin.grids.create');
Route::get('grids/{projects}/expeditions/{expeditions}', [GridsController::class, 'expeditionsShow'])->name('admin.grids.show');
Route::get('grids/{projects}/expeditions/{expeditions}/edit', [GridsController::class, 'expeditionsEdit'])->name('admin.grids.edit');
Route::post('grids/{projects}/expeditions/{expeditions}/export', [GridsController::class, 'export'])->name('admin.grids.expedition.export');

