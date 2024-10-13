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

use App\Http\Controllers\Admin\GeolocateCommunityController;
use App\Http\Controllers\Admin\GeoLocateController;
use App\Http\Controllers\Admin\GeolocateExportController;
use App\Http\Controllers\Admin\GeolocateFieldsController;
use App\Http\Controllers\Admin\GeolocateFormController;
use App\Http\Controllers\Admin\GeoLocateStatController;

Route::post('geolocates/{expeditions}/store', [GeoLocateController::class, 'store'])->name('admin.geolocates.store');
Route::delete('geolocates/{expeditions}/destroy', [GeoLocateController::class, 'destroy'])->name('admin.geolocates.destroy');

Route::get('geolocates/stats/{geoLocateDataSource}', [GeoLocateStatController::class, 'index'])->name('admin.geolocate-stats.index');
Route::post('geolocates/{geoLocateDataSource}/update', [GeoLocateStatController::class, 'update'])->name('admin.geolocate-stats.update');

Route::get('geolocates/form/{expedition}', [GeoLocateFormController::class, 'index'])->name('admin.geolocate-form.index');
Route::post('geolocates/form/{expedition}/show', [GeoLocateFormController::class, 'show'])->name('admin.geolocate-form.show');

Route::post('geolocates/fields/{expedition}', GeoLocateFieldsController::class)->name('admin.geolocate-fields.index');

Route::get('geolocates/community/{expedition}/edit', [GeoLocateCommunityController::class, 'edit'])->name('admin.geolocate-community.edit');
Route::post('geolocates/community/{expedition}/update', [GeoLocateCommunityController::class, 'update'])->name('admin.geolocate-community.update');

Route::post('geolocates/{expeditions}/export', GeoLocateExportController::class)->name('admin.geolocate-export.index');
