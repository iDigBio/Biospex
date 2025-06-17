<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expedition;
use Artisan;
use Redirect;
use Throwable;

class ExpeditionExportController extends Controller
{
    /**
     * Generate export download.
     *
     * @see \App\Console\Commands\ExportQueueCommand
     */
    public function __invoke(Expedition $expedition): \Illuminate\Http\RedirectResponse
    {
        try {
            Artisan::call('export:queue', ['expeditionId' => $expedition->id]);
            $status = 'success';
            $message = t('Export generation has been added to the job queue. You will be notified when completed.');
        } catch (Throwable $throwable) {
            $status = 'error';
            $message = t('An error occurred while trying to generate the download. Please contact the administration with this error and the title of the Expedition.');
        }

        return Redirect::route('admin.expeditions.show', [$expedition])->with($status, $message);
    }
}
