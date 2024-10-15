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

use App\Http\Controllers\Controller;
use App\Services\Image\Thumbnail;

/**
 * Class ImageController
 */
class ImageController extends Controller
{
    /**
     * @var Thumbnail
     */
    public $thumbnail;

    /**
     * Construct
     * TODO: refactor this
     */
    public function __construct(
        Thumbnail $thumbnail
    ) {
        $this->thumbnail = $thumbnail;
    }

    /**
     * Return resized image
     *
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function preview()
    {
        if (\Request::has('url-view')) {
            return \Request::input('url');
        }

        $url = \Request::input('url');
        $thumb = $this->thumbnail->getThumbnail(urldecode($url));

        return '<img src="data:image/jpeg;base64,'.base64_encode($thumb).'" />';
    }
}
