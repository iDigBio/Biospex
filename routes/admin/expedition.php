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

use App\Http\Controllers\Admin\ExpeditionController;

Route::get('expeditions', [ExpeditionController::class, 'index'])->name('admin.expeditions.index');
Route::post('expeditions/sort', [ExpeditionController::class, 'sort'])->name('admin.expeditions.sort');
Route::get('projects/{projects}/expeditions/create', [ExpeditionController::class, 'create'])->name('admin.expeditions.create');
Route::post('projects/{projects}/expeditions', [ExpeditionController::class, 'store'])->name('admin.expeditions.store');
Route::get('projects/{projects}/expeditions/{expeditions}', [ExpeditionController::class, 'show'])->name('admin.expeditions.show');


Route::get('projects/{projects}/expeditions/{expeditions}/edit', [ExpeditionController::class, 'edit'])->name('admin.expeditions.edit');
Route::put('projects/{projects}/expeditions/{expeditions}', [ExpeditionController::class, 'update'])->name('admin.expeditions.update');
Route::delete('projects/{projects}/expeditions/{expeditions}', [ExpeditionController::class, 'delete'])->name('admin.expeditions.delete');

Route::get('projects/{projects}/expeditions/{expeditions}/clone', [ExpeditionController::class, 'clone'])->name('admin.expeditions.clone');
Route::post('projects/{projects}/expeditions/{expeditions}/process', [ExpeditionController::class, 'process'])->name('admin.expeditions.process');
Route::delete('projects/{projects}/expeditions/{expeditions}/stop', [ExpeditionController::class, 'stop'])->name('admin.expeditions.stop');
Route::post('projects/{projects}/expeditions/{expeditions}/ocr', [ExpeditionController::class, 'ocr'])->name('admin.expeditions.ocr');

Route::post('projects/{projects}/expeditions/{expeditions}/workflow', [ExpeditionController::class, 'workflowId'])->name('admin.expeditions.workflowId');
