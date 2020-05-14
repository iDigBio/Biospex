<?php

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
     * @param string $bingoId
     * @return \Illuminate\View\View|string
     */
    public function generate(string $bingoId)
    {
        $bingo = $this->bingoService->findBingoWith($bingoId, ['project', 'words']);
        if (!$bingo) {
            return __('message.bingo_not_found');
        }

        $rows = $this->bingoService->generateBingoCard($bingo);

        BingoJob::dispatch($bingoId);

        return view('front.bingo.card', compact('bingo', 'rows'));
    }
}