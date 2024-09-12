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

use App\Http\Controllers\Front\WeDigBioController;
use App\Http\Controllers\Front\WeDigBioProgressController;
use App\Http\Controllers\Front\WeDigBioProjectsController;
use App\Http\Controllers\Front\WeDigBioRateController;

Route::get('wedigbio', [WeDigBioController::class, 'index'])->name('front.wedigbio.index');
Route::get('wedigbio/progress/{event?}', WeDigBioProgressController::class)->name('front.wedigbio-progress');
Route::get('wedigbio/rate/{event?}', WeDigBioRateController::class)->name('front.get.wedigbio-rate');
Route::get('wedigbio/projects/{event?}', WeDigBioProjectsController::class)->name('front.get.wedigbio-projects');
