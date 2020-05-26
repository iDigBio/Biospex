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

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\TeamCategory;

class TeamsController extends Controller
{

    /**
     * @var TeamCategory
     */
    public $teamCategoryContract;

    /**
     * TeamsController constructor.
     * 
     * @param TeamCategory $teamCategoryContract
     */
    public function __construct(TeamCategory $teamCategoryContract)
    {
        $this->teamCategoryContract = $teamCategoryContract;
    }

    /**
     * Show categories.
     * 
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $categories = $this->teamCategoryContract->getTeamIndexPage();

        return view('front.team.index', compact('categories'));
    }
}
