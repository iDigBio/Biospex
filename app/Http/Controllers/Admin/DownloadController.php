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

namespace App\Http\Controllers\Admin;

use FlashHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

/**
 * Class DownloadController
 *
 * @package App\Http\Controllers\Admin
 */
class DownloadController extends Controller
{
    /**
     * Download report.
     *
     * @param string $file
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     * @throws \League\Csv\Exception
     */
    public function report(string $file)
    {
        $fileName = base64_decode($file);

        if(! Storage::exists(config('config.reports_dir') . '/' . $fileName)) {
            FlashHelper::warning( t('Report file does not exist.'));
            return redirect()->route('admin.ingest.index');
        }

        $filePath = Storage::path(config('config.reports_dir') . '/' . $fileName);
        $reader = Reader::createFromPath($filePath, 'r');
        $reader->setOutputBOM(Reader::BOM_UTF8);

        $headers = [
            'Content-Encoding' => 'none',
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"'
        ];

        return response($reader->toString(), 200, $headers);
    }

    /**
     * Download export file.
     *
     * @param string $file
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     * @throws \League\Csv\Exception
     */
    public function export(string $file)
    {
        $fileName = base64_decode($file);

        if(! Storage::exists(config('config.rapid_export_dir') . '/' . $fileName)) {
            FlashHelper::warning( t('RAPID export file does not exist.'));
            return redirect()->route('admin.export.index');
        }

        $filePath = Storage::path(config('config.rapid_export_dir') . '/' . $fileName);
        $reader = Reader::createFromPath($filePath, 'r');
        $reader->setOutputBOM(Reader::BOM_UTF8);

        $headers = [
            'Content-Encoding' => 'none',
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"'
        ];

        return response($reader->toString(), 200, $headers);
    }

    /**
     * Download version file.
     *
     * @param string $file
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function version(string $file)
    {
        $fileName = base64_decode($file);

        if(! Storage::exists(config('config.rapid_version_dir') . '/' . $fileName)) {
            FlashHelper::warning( t('RAPID version file does not exist.'));
            return redirect()->route('admin.version.index');
        }

        $filePath = Storage::path(config('config.rapid_version_dir') . '/' . $fileName);

        $headers = [
            'Content-Type'        => 'application/octet-stream',
            'Content-disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Description' => 'File Transfer'
        ];

        return response()->download($filePath, $fileName, $headers);
    }


}
