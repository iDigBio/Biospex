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

use App\Http\Controllers\Admin\GroupController;

Route::get('groups', [GroupController::class, 'index'])->name('admin.groups.index');
Route::get('groups/create', [GroupController::class, 'create'])->name('admin.groups.create');
Route::post('groups', [GroupController::class, 'store'])->name('admin.groups.store');
Route::get('groups/{groups}', [GroupController::class, 'show'])->name('admin.groups.show');
Route::get('groups/{groups}/edit', [GroupController::class, 'edit'])->name('admin.groups.edit');
Route::put('groups/{groups}', [GroupController::class, 'update'])->name('admin.groups.update');
Route::delete('groups/{groups}', [GroupController::class, 'delete'])->name('admin.groups.delete');
Route::delete('groups/{groups}/{user}', [GroupController::class, 'deleteGroupUser'])->name('admin.groups.deleteUser');
Route::delete('groups/{groups}/geolocate/{form}', [GroupController::class, 'deleteGeoLocateForm'])->name('admin.groups.deleteForm');
