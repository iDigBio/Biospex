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

namespace App\Presenters;

use Illuminate\Support\Facades\Storage;


class ProductPresenter extends Presenter
{
    /**
     * Generates a temporary download URL for a ZIP file stored in S3.
     *
     * The file path is constructed using the model's key and a predefined directory
     * from the application configuration. The URL is temporary and valid for 30 minutes.
     * The response includes a Content-Disposition header indicating an attachment download
     * with the file name derived from the model's name.
     *
     * @return string The temporary URL for the ZIP file.
     */
    public function download(): string
    {
        $filePath = config('config.rapid_product_dir').'/'.$this->model->key.'.zip';
        $timestamp = now()->addMinutes(30);
        $response = ['ResponseContentDisposition' => 'attachment;filename='.$this->model->name.'.zip'];

        return Storage::disk('s3')->temporaryUrl($filePath, $timestamp, $response);
    }
}
