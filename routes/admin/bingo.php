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

use App\Http\Controllers\Admin\BingoController;

Route::get('bingos', [BingoController::class, 'index'])->name('admin.bingos.index');
Route::post('bingos/sort/', [BingoController::class, 'sort'])->name('admin.bingos.sort');
Route::get('bingos/create', [BingoController::class, 'create'])->name('admin.bingos.create');
Route::post('bingos/create', [BingoController::class, 'store'])->name('admin.bingos.store');
Route::get('bingos/{bingos}', [BingoController::class, 'show'])->name('admin.bingos.show');
Route::get('bingos/{bingos}/edit', [BingoController::class, 'edit'])->name('admin.bingos.edit');
Route::put('bingos/{bingos}', [BingoController::class, 'update'])->name('admin.bingos.update');
Route::delete('bingos/{bingos}', [BingoController::class, 'delete'])->name('admin.bingos.delete');
