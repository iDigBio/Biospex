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

Route::get('projects/{projects}/expeditions/{expeditions}/geolocates/', [GeoLocateController::class, 'index'])->name('admin.geolocates.stats');
Route::get('projects/{projects}/expeditions/{expeditions}/geolocates/show', [GeoLocateController::class, 'show'])->name('admin.geolocates.show');
Route::post('projects/{projects}/expeditions/{expeditions}/geolocates/form', [GeoLocateController::class, 'form'])->name('admin.geolocates.form');
Route::post('projects/{projects}/expeditions/{expeditions}/geolocates/fields', [GeoLocateController::class, 'fields'])->name('admin.geolocates.fields');

Route::get('projects/{projects}/expeditions/{expeditions}/geolocates/community', [GeoLocateController::class, 'communityForm'])->name('admin.geolocates.communityForm');
Route::post('projects/{projects}/expeditions/{expeditions}/geolocates/community', [GeoLocateController::class, 'community'])->name('admin.geolocates.community');

Route::post('projects/{projects}/expeditions/{expeditions}/geolocates/store', [GeoLocateController::class, 'store'])->name('admin.geolocates.store');
Route::post('projects/{projects}/expeditions/{expeditions}/geolocates/export', [GeoLocateController::class, 'export'])->name('admin.geolocates.export');
Route::delete('projects/{projects}/expeditions/{expeditions}/geolocates/delete', [GeoLocateController::class, 'delete'])->name('admin.geolocates.delete');

Route::post('projects/{projects}/expeditions/{expeditions}/geolocates/refresh', [GeoLocateController::class, 'refresh'])->name('admin.geolocates.refresh');

Route::post('projects/{projects}/expeditions/{expeditions}/geolocates/source', [GeoLocateController::class, 'source'])->name('admin.geolocates.source-upload');