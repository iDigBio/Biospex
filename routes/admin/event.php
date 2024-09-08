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

use App\Http\Controllers\Admin\EventController;

Route::get('events', [EventController::class, 'index'])->name('admin.events.index');
Route::post('events/sort/', [EventController::class, 'sort'])->name('admin.events.sort');
Route::get('events/create', [EventController::class, 'create'])->name('admin.events.create');
Route::post('events/create', [EventController::class, 'store'])->name('admin.events.store');
Route::get('events/{events}', [EventController::class, 'show'])->name('admin.events.show');
Route::get('events/{events}/edit', [EventController::class, 'edit'])->name('admin.events.edit');
Route::put('events/{events}', [EventController::class, 'update'])->name('admin.events.update');
Route::delete('events/{events}', [EventController::class, 'delete'])->name('admin.events.delete');
Route::get('events/{events}/transcriptions', [EventController::class, 'exportTranscriptions'])->name('admin.events.exportTranscriptions');
Route::get('events/{events}/users', [EventController::class, 'exportUsers'])->name('admin.events.exportUsers');
