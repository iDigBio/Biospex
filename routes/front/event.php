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

use App\Http\Controllers\Front\EventController;
use App\Http\Controllers\Front\EventRateChartController;
use App\Http\Controllers\Front\EventScoreboardController;

// Event routes for front pages and signup
Route::get('events', [EventController::class, 'index'])->name('front.events.index');
Route::post('events/sort/', [EventController::class, 'sort'])->name('front.events.sort');
Route::get('events/{event}', [EventController::class, 'read'])->name('front.events.read');
Route::get('events/{uuid}/signup', [EventController::class, 'signup'])->name('front.events.signup');
Route::post('events/{uuid}/join', [EventController::class, 'join'])->name('front.events.join');

// Event scoreboard and rate chart
Route::get('ajax/scoreboard/{event}', [EventScoreboardController::class, 'show'])->name('event.get.scoreboard');
Route::get('ajax/rate/{event}/{timestamp?}', [EventRateChartController::class, 'index'])->name('event.rate.index');
