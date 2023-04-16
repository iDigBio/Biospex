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

use App\Http\Controllers\Front\AjaxController;

Route::get('ajax/chart/{project}', [AjaxController::class, 'loadAmChart'])->name('ajax.get.chart');
Route::get('ajax/scoreboard/{event}', [AjaxController::class, 'scoreboard'])->name('ajax.get.scoreboard');
Route::get('ajax/step/{event}/{load?}', [AjaxController::class, 'eventStepChart'])->name('ajax.get.step');
Route::get('poll', [AjaxController::class, 'poll'])->name('ajax.get.poll');
Route::get('bingos/{bingo}/winner/{map}', [AjaxController::class, 'bingoWinner'])->name('ajax.get.bingoWinner');
Route::get('ajax/wedigbio-progress/{dateId?}', [AjaxController::class, 'weDigBioProgress'])->name('ajax.get.wedigbio-progress');
Route::get('ajax/wedigbio-rate/{dateId}/{load?}', [AjaxController::class, 'weDigBioRate'])->name('ajax.get.wedigbio-rate');
Route::get('ajax/wedigbio-projects', [AjaxController::class, 'getProjectsForWeDigBioRateChart'])->name('ajax.get.wedigbio-projects');


