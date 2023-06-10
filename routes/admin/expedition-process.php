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

use App\Http\Controllers\Admin\ExpeditionProcessController;

Route::post('expeditions/sort', [ExpeditionProcessController::class, 'sort'])->name('admin.expeditions.sort');
Route::post('projects/{projects}/expeditions/{expeditions}/process', [ExpeditionProcessController::class, 'process'])->name('admin.expeditions.process');
Route::delete('projects/{projects}/expeditions/{expeditions}/stop', [ExpeditionProcessController::class, 'stop'])->name('admin.expeditions.stop');
Route::post('projects/{projects}/expeditions/{expeditions}/ocr', [ExpeditionProcessController::class, 'ocr'])->name('admin.expeditions.ocr');
Route::post('projects/{projects}/expeditions/{expeditions}/workflow', [ExpeditionProcessController::class, 'workflowId'])->name('admin.expeditions.workflowId');
