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

use App\Http\Controllers\Admin\DownloadController;

Route::get('projects/{projects}/expeditions/{expeditions}/downloads', [DownloadController::class, 'index'])->name('admin.downloads.index');
Route::get('reports/{file}', [DownloadController::class, 'report'])->name('admin.downloads.report');
Route::get('projects/{projects}/expeditions/{expeditions}/downloads/{downloads}', [DownloadController::class, 'download'])->name('admin.downloads.download');
Route::get('projects/{projects}/expeditions/{expeditions}/downloads/{files}/zip-batch', [DownloadController::class, 'downloadZipBatch'])->name('admin.downloads.downloadZipBatch');
Route::get('projects/{projects}/expeditions/{expeditions}/downloads/{files}/tar-batch', [DownloadController::class, 'downloadTarBatch'])->name('admin.downloads.downloadTarBatch');
Route::get('projects/{projects}/expeditions/{expeditions}/export', [DownloadController::class, 'export'])->name('admin.downloads.export');
Route::get('projects/{projects}/expeditions/{expeditions}/downloads/{downloads}/batch', [DownloadController::class, 'batch'])->name('admin.downloads.batch');
Route::get('projects/{projects}/expeditions/{expeditions}/summary', [DownloadController::class, 'summary'])->name('admin.downloads.summary');
