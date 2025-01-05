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

use App\Http\Controllers\Controller;
use App\Models\Bingo;
use App\Services\Bingo\BingoService;
use View;

/**
 * Class BingoController
 */
class BingoController extends Controller
{
    /**
     * BingoController constructor.
     */
    public function __construct(protected BingoService $bingoService) {}

    /**
     * Display admin index for bingo games created by user.
     */
    public function index(): \Illuminate\View\View
    {
        $bingos = $this->bingoService->getFrontIndex();

        return View::make('front.bingo.index', compact('bingos'));
    }

    /**
     * Bingo show.
     */
    public function show(Bingo $bingo): \Illuminate\View\View
    {
        $this->bingoService->showPublicBingo($bingo);

        return View::make('front.bingo.show', compact('bingo'));
    }
}
