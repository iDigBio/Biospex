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

namespace App\Http\Controllers\Front;

use FlashHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

/**
 * Class DownloadController
 *
 * @package App\Http\Controllers\Admin
 */
class DownloadController extends Controller
{
    /**
     * Download product file.
     *
     * @param string $file
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function product(string $file)
    {
        $fileName = base64_decode($file);

        if(! Storage::exists(config('config.rapid_product_dir') . '/' . $fileName)) {
            FlashHelper::warning( t('RAPID product file does not exist.'));
            return redirect()->route('admin.product.index');
        }

        $filePath = Storage::path(config('config.rapid_product_dir') . '/' . $fileName);

        $headers = [
            'Content-Type'        => 'application/octet-stream',
            'Content-disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Description' => 'File Transfer'
        ];

        return response()->download($filePath, $fileName, $headers);
    }


}
