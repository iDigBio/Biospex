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

use App\Http\Controllers\Admin\ExpeditionsController;

Route::get('expeditions', [ExpeditionsController::class, 'index'])->name('admin.expeditions.index');
Route::post('expeditions/sort', [ExpeditionsController::class, 'sort'])->name('admin.expeditions.sort');
Route::get('projects/{projects}/expeditions/create', [ExpeditionsController::class, 'create'])->name('admin.expeditions.create');
Route::post('projects/{projects}/expeditions', [ExpeditionsController::class, 'store'])->name('admin.expeditions.store');
Route::get('projects/{projects}/expeditions/{expeditions}', [ExpeditionsController::class, 'show'])->name('admin.expeditions.show');


Route::get('projects/{projects}/expeditions/{expeditions}/edit', [ExpeditionsController::class, 'edit'])->name('admin.expeditions.edit');
Route::put('projects/{projects}/expeditions/{expeditions}', [ExpeditionsController::class, 'update'])->name('admin.expeditions.update');
Route::delete('projects/{projects}/expeditions/{expeditions}', [ExpeditionsController::class, 'delete'])->name('admin.expeditions.delete');

Route::get('projects/{projects}/expeditions/{expeditions}/clone', [ExpeditionsController::class, 'clone'])->name('admin.expeditions.clone');
Route::post('projects/{projects}/expeditions/{expeditions}/process', [ExpeditionsController::class, 'process'])->name('admin.expeditions.process');
Route::delete('projects/{projects}/expeditions/{expeditions}/stop', [ExpeditionsController::class, 'stop'])->name('admin.expeditions.stop');
Route::post('projects/{projects}/expeditions/{expeditions}/ocr', [ExpeditionsController::class, 'ocr'])->name('admin.expeditions.ocr');
