<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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

use App\Http\Controllers\Controller;
use App\Jobs\BingoJob;
use App\Models\Bingo;
use App\Services\Bingo\BingoService;
use Illuminate\Support\Facades\View;
use JavaScript;

class BingoJoinController extends Controller
{
    public function __construct(protected BingoService $bingoService) {}

    public function index(Bingo $bingo): \Illuminate\View\View
    {
        $bingo->load('project', 'words');
        $bingoUser = $this->bingoService->getOrCreateBingoUser($bingo);
        $bingoUserData = $this->bingoService->getBingoUserData($bingo);

        JavaScript::put([
            'channel' => config('config.poll_bingo_channel').'.'.$bingo->uuid,
            'rowsUrl' => route('front.bingos.create', $bingo),
            'winnerUrl' => route('front.get.bingo-winner', [$bingo, $bingoUser]),
            'bingoUserUuid' => $bingoUser->uuid,
            'bingoUserData' => $bingoUserData,
        ]);

        BingoJob::dispatch($bingo, $bingoUser);

        return View::make('front.bingo.card', compact('bingo'));
    }

    /**
     * Generate bingo card.
     */
    public function create(Bingo $bingo): \Illuminate\View\View
    {
        $bingo->load('project', 'words');
        $rows = $this->bingoService->generateBingoCard($bingo);

        return View::make('front.bingo.partials.card-rows', ['project' => $bingo->project, 'rows' => $rows]);
    }
}
