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

use App\Http\Controllers\Admin\InviteController;

Route::get('groups/{groups}/invites', [InviteController::class, 'index'])->name('admin.invites.index');
Route::post('groups/{groups}/invites', [InviteController::class, 'store'])->name('admin.invites.store');
Route::delete('groups/{groups}/invites/{invites}', [InviteController::class, 'delete'])->name('admin.invites.delete');