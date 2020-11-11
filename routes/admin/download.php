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

use App\Http\Controllers\Admin\DownloadsController;

Route::get('projects/{projects}/expeditions/{expeditions}/downloads', [DownloadsController::class, 'index'])->name('admin.downloads.index');
Route::get('reports/{file}', [DownloadsController::class, 'report'])->name('admin.downloads.report');
Route::get('projects/{projects}/expeditions/{expeditions}/downloads/{downloads}', [DownloadsController::class, 'download'])->name('admin.downloads.download');
Route::get('projects/{projects}/expeditions/{expeditions}/downloads/{downloads}/tar', [DownloadsController::class, 'downloadTar'])->name('admin.downloads.downloadTar');
Route::get('projects/{projects}/expeditions/{expeditions}/downloads/{files}/tar-batch', [DownloadsController::class, 'downloadTarBatch'])->name('admin.downloads.downloadTarBatch');
Route::get('projects/{projects}/expeditions/{expeditions}/regenerate', [DownloadsController::class, 'regenerate'])->name('admin.downloads.regenerate');
Route::get('projects/{projects}/expeditions/{expeditions}/downloads/{downloads}/batch', [DownloadsController::class, 'batch'])->name('admin.downloads.batch');
Route::get('projects/{projects}/expeditions/{expeditions}/summary', [DownloadsController::class, 'summary'])->name('admin.downloads.summary');