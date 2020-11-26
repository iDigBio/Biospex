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

use App\Http\Controllers\Admin\ImportController;

Route::get('projects/{projects}/import', [ImportController::class, 'index'])->name('admin.imports.index');
Route::post('projects/dwcfile', [ImportController::class, 'dwcFile'])->name('admin.imports.dwcfile');
Route::post('projects/recordset', [ImportController::class, 'recordSet'])->name('admin.imports.recordset');
Route::post('projects/dwcuri', [ImportController::class, 'dwcUri'])->name('admin.imports.dwcuri');