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

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RecordsetImportJob;
use Auth;
use Redirect;
use Request;
use Throwable;

class ImportRecordSetController extends Controller
{
    /**
     * Upload record set.
     */
    public function __invoke(): \Illuminate\Http\RedirectResponse
    {
        try {
            $projectId = Request::input('project_id');

            $data = [
                'id' => Request::input('recordset'),
                'user_id' => Auth::user()->id,
                'project_id' => $projectId,
            ];

            RecordsetImportJob::dispatch($data);

            return Redirect::back()->with('success', t('Upload was successful. You will receive an email when your import data have been processed.'));
        } catch (Throwable $throwable) {
            return Redirect::back()->with('error', t('Error uploading file. %', $throwable->getMessage()));
        }
    }
}
