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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
// Event signup routes
use App\Http\Controllers\Front\EventTeamUserController;

Route::get('event-team/{team}/signup', [EventTeamUserController::class, 'create'])
    ->name('front.events_team_user.create');
Route::post('event-team/{team}/join', [EventTeamUserController::class, 'store'])
    ->name('front.events_team_user.store');
