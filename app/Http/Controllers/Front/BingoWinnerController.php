<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Jobs\BingoJob;
use App\Models\Bingo;
use App\Models\BingoMap;

class BingoWinnerController extends Controller
{
    /**
     * Trigger bingo winner.
     */
    public function __invoke(Bingo $bingo, BingoMap $bingoMap)
    {
        if (\Request::ajax()) {
            BingoJob::dispatch($bingo, $bingoMap);
        }
    }
}
