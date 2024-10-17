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

use App\Http\Controllers\Admin\PanoptesWorkflowFormController;

Route::get('expeditions/{expedition}/workflow', [PanoptesWorkflowFormController::class, 'edit'])->name('admin.panoptes-workflow.edit');
Route::post('expeditions/{expedition}/workflow', [PanoptesWorkflowFormController::class, 'update'])->name('admin.panoptes-workflow.update');
