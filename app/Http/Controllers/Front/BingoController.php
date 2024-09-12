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
use App\Jobs\BingoJob;
use App\Models\Bingo;
use App\Services\Bingo\BingoService;
use Illuminate\Support\Facades\View;

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
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $bingos = $this->bingoService->bingo->with(['user', 'project'])->get();

        return View::make('front.bingo.index', compact('bingos'));
    }

    /**
     * Generate bingo card.
     *
     * @return \Illuminate\View\View|string
     */
    public function create(Bingo $bingo)
    {
        $bingo->load('project', 'words');

        $rows = $this->bingoService->generateBingoCard($bingo);

        BingoJob::dispatch($bingo);

        return View::make('front.bingo.card', compact('bingo', 'rows'));
    }

    /**
     * Bingo show.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function show(Bingo $bingo)
    {
        [$bingo, $words] = $this->bingoService->showBingo($bingo);

        return View::make('front.bingo.show', compact('bingo', 'words'));
    }
}
