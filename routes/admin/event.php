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

use App\Http\Controllers\Admin\EventsController;

Route::get('events', [EventsController::class, 'index'])->name('admin.events.index');
Route::post('events/sort/', [EventsController::class, 'sort'])->name('admin.events.sort');
Route::get('events/create', [EventsController::class, 'create'])->name('admin.events.create');
Route::post('events/create', [EventsController::class, 'store'])->name('admin.events.store');
Route::get('events/{events}', [EventsController::class, 'show'])->name('admin.events.show');
Route::get('events/{events}/edit', [EventsController::class, 'edit'])->name('admin.events.edit');
Route::put('events/{events}', [EventsController::class, 'update'])->name('admin.events.update');
Route::delete('events/{events}', [EventsController::class, 'delete'])->name('admin.events.delete');
Route::get('events/{events}/transcriptions', [EventsController::class, 'exportTranscriptions'])->name('admin.events.exportTranscriptions');
Route::get('events/{events}/users', [EventsController::class, 'exportUsers'])->name('admin.events.exportUsers');