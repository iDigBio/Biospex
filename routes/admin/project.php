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

use App\Http\Controllers\Admin\OcrController;
use App\Http\Controllers\Admin\ProjectController;

Route::get('projects', [ProjectController::class, 'index'])->name('admin.projects.index');
Route::get('projects/create', [ProjectController::class, 'create'])->name('admin.projects.create');
Route::post('projects/create', [ProjectController::class, 'store'])->name('admin.projects.store');
Route::get('projects/{projects}', [ProjectController::class, 'show'])->name('admin.projects.show');
Route::get('projects/{projects}/edit', [ProjectController::class, 'edit'])->name('admin.projects.edit');
Route::put('projects/{projects}', [ProjectController::class, 'update'])->name('admin.projects.update');
Route::get('projects/{projects}/clone', [ProjectController::class, 'clone'])->name('admin.projects.clone');
Route::get('projects/{projects}/explore', [ProjectController::class, 'explore'])->name('admin.projects.explore');
Route::get('projects/{projects}/statistics', [ProjectController::class, 'statistics'])->name('admin.projects.statistics');

Route::post('projects/sort', [ProjectController::class, 'sort'])->name('admin.projects.sort');

Route::delete('projects/{projects}', [ProjectController::class, 'delete'])->name('admin.projects.delete');
Route::delete('projects/{projects}/subject', [ProjectController::class, 'deleteSubjects'])->name('admin.projects.deleteSubjects');
