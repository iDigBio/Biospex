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

namespace App\Http\Controllers\Front;

use Illuminate\Contracts\Console\Kernel as Artisan;
use Illuminate\Http\Request;

class PollController
{
    public function __construct(protected Artisan $artisan, protected Request $request) {}

    /**
     * Call polling command when process modal opened. Trigger inside biospex.js
     */
    public function index()
    {
        if ($this->request->ajax()) {
            $this->artisan->call('ocr:poll');
            $this->artisan->call('export:poll');
        }
    }
}
