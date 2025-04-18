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

namespace App\Http\ViewComposers;

use App\Models\WeDigBioEvent;
use Illuminate\Contracts\View\View;

class NavComposer
{
    /**
     * Create a new view composer.
     */
    public function __construct(protected WeDigBioEvent $weDigBioEvent) {}

    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $event = $this->weDigBioEvent->where('active', 1)->first();

        $view->with('event', $event);
    }
}
