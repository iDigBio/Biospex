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
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{

    /**
     * Download report.
     *
     * @param $fileName
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function report($fileName)
    {
        if(! Storage::exists(config('config.reports_dir') . '/' . $fileName)) {
            Flash::warning( t('Report file does not exist.'));
            return redirect()->route('admin.ingest.index');
        }

        $headers = [
            'Content-Type'        => 'application/x-compressed',
            'Content-disposition' => 'attachment; filename="'.$fileName.'"',
        ];

        $file = Storage::path(config('config.reports_dir') . '/' . $fileName);

        return response()->download($file, $fileName, $headers);
    }

    /**
     * Download export file.
     *
     * @param $fileName
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export($fileName)
    {
        if(! Storage::exists(config('config.rapid_export_dir') . '/' . $fileName)) {
            Flash::warning( t('RAPID export file does not exist.'));
            return redirect()->route('admin.export.index');
        }

        $headers = [
            'Content-Type'        => 'application/x-compressed',
            'Content-disposition' => 'attachment; filename="'.$fileName.'"',
        ];

        $file = Storage::path(config('config.rapid_export_dir') . '/' . $fileName);

        return response()->download($file, $fileName, $headers);
    }
}
