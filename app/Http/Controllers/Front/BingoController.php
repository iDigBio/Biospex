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
use App\Services\Games\BingoProcess;

/**
 * Class BingoController
 *
 * @package App\Http\Controllers\Front
 */
class BingoController extends Controller
{
    /**
     * @var \App\Services\Games\BingoProcess
     */
    private $bingoProcess;

    /**
     * BingoController constructor.
     *
     * @param \App\Services\Games\BingoProcess $bingoProcess
     */
    public function __construct(BingoProcess $bingoProcess)
    {
        $this->bingoProcess = $bingoProcess;
    }

    /**
     * Display admin index for bingo games created by user.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $bingos = $this->bingoProcess->getAllBingos();

        return \View::make('front.bingo.index', compact('bingos'));
    }

    /**
     * Bingo show.
     *
     * @param string $bingoId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function show(string $bingoId)
    {
        [$bingo, $words] = $this->bingoProcess->showBingo($bingoId);

        return \View::make('front.bingo.show', compact('bingo', 'words'));
    }

    /**
     * Generate bingo card.
     *
     * @param string $bingoId
     * @return \Illuminate\View\View|string
     */
    public function generate(string $bingoId)
    {
        $bingo = $this->bingoProcess->findBingoWith($bingoId, ['project', 'words']);
        if (!$bingo) {
            return t('Bingo Game could not be found.');
        }

        $rows = $this->bingoProcess->generateBingoCard($bingo);

        BingoJob::dispatch($bingoId);

        return \View::make('front.bingo.card', compact('bingo', 'rows'));
    }
}