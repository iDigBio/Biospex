<?php
/**
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

namespace App\Http\Controllers\Admin;

use Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\RapidImportFormRequest;
use App\Http\Requests\RapidUpdateFormRequest;
use App\Jobs\RapidImportJob;
use Auth;

class ImportController extends Controller
{
    /**
     * IndexController constructor.
     */
    public function __construct()
    {

    }

    /**
     * Show projects list for admin page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('import');
    }

    /**
     * Import original rapid data.
     *
     * @param \App\Http\Requests\RapidImportFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(RapidImportFormRequest $request)
    {
        $path = $request->file('import-file')->store('imports/rapid');

        RapidImportJob::dispatch(Auth::user(), $path);

        Flash::success(__('pages.rapid_import_success_msg'));

        return redirect()->route('admin.get.index');
    }

    public function update(RapidUpdateFormRequest $request)
    {

    }
}
