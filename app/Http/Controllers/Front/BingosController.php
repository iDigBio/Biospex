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
use App\Jobs\BingoJob;
use App\Services\Model\BingoService;

/**
 * Class BingosController
 *
 * @package App\Http\Controllers\Front
 */
class BingosController extends Controller
{
    /**
     * @var \App\Services\Model\BingoService
     */
    private $bingoService;

    /**
     * BingosController constructor.
     *
     * @param \App\Services\Model\BingoService $bingoService
     */
    public function __construct(BingoService $bingoService)
    {
        $this->bingoService = $bingoService;
    }

    /**
     * Display admin index for bingo games created by user.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $bingos = $this->bingoService->getAllBingos();

        return view('front.bingo.index', compact('bingos'));
    }

    /**
     * Bingo show.
     *
     * @param string $bingoId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function show(string $bingoId)
    {
        [$bingo, $words] = $this->bingoService->showBingo($bingoId);

        return view('front.bingo.show', compact('bingo', 'words'));
    }

    /**
     * Generate bingo card.
     *
     * @param string $bingoId
     * @return \Illuminate\View\View|string
     */
    public function generate(string $bingoId)
    {
        $bingo = $this->bingoService->findBingoWith($bingoId, ['project', 'words']);
        if (!$bingo) {
            return t('Bingo Game could not be found.');
        }

        $rows = $this->bingoService->generateBingoCard($bingo);

        BingoJob::dispatch($bingoId);

        return view('front.bingo.card', compact('bingo', 'rows'));
    }
}