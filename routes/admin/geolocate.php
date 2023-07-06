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

use App\Http\Controllers\Admin\GeoLocateController;

//Route::get('projects/{projects}/expeditions/{expeditions}/geolocates', [GeoLocateController::class, 'index'])->name('admin.geolocate.index');
Route::get('projects/{projects}/expeditions/{expeditions}/geolocates/', [GeoLocateController::class, 'index'])->name('admin.geolocate.index');
Route::post('projects/{projects}/expeditions/{expeditions}/geolocates/', [GeoLocateController::class, 'index'])->name('admin.geolocate.form');
Route::post('projects/{projects}/expeditions/{expeditions}/geolocates/store', [GeoLocateController::class, 'store'])->name('admin.geolocate.store');
Route::post('projects/{projects}/expeditions/{expeditions}/geolocates/process', [GeoLocateController::class, 'process'])->name('admin.geolocate.process');
Route::delete('projects/{projects}/expeditions/{expeditions}/geolocates/delete', [GeoLocateController::class, 'delete'])->name('admin.geolocate.delete');