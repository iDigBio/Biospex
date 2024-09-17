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

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserPasswordController;

Route::get('users', function () {
    return redirect()->route('admin.users.edit', [Auth::user()]);
})->name('admin.users.index');

Route::get('users/{user}', function () {
    return redirect()->route('admin.users.edit', [Auth::user()]);
})->name('admin.users.show');

Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
Route::put('users/{user}', [UserController::class, 'update'])->name('admin.users.update');

Route::put('password/{user}/pass', UserPasswordController::class)->name('admin.users.password');
