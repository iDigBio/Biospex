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

namespace App\Http\ViewComposers;

use App\Models\Notice;
use Illuminate\Contracts\View\View;

/**
 * Class NoticesComposer
 */
class NoticesComposer
{
    /**
     * Create a new profile composer.
     */
    public function __construct(protected Notice $notice) {}

    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $notices = $this->notice->where('enabled', 1)->get();
        $notices = $notices->isEmpty() ? null : $notices;

        $view->with('notices', $notices);
    }
}
