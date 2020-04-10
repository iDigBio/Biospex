<?php

namespace App\Http\Controllers\Front;

use App\Facades\FlashHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\BingoFormRequest;
use App\Repositories\Interfaces\Bingo;
use App\Repositories\Interfaces\Project;

class BingosController extends Controller
{
    /**
     * @var \App\Repositories\Interfaces\Bingo
     */
    private $bingoContract;

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $projectContract;

    /**
     * BingosController constructor.
     *
     * @param \App\Repositories\Interfaces\Bingo $bingoContract
     * @param \App\Repositories\Interfaces\Project $projectContract
     */
    public function __construct(Bingo $bingoContract, Project $projectContract)
    {
        $this->bingoContract = $bingoContract;
        $this->projectContract = $projectContract;
    }

    /**
     * Display admin index for bingo games created by user.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $bingos = $this->bingoContract->allWith(['user']);

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
        $bingo = $this->bingoContract->findWith($bingoId, ['words', 'user']);

        $words = $bingo->words->chunk(3);

        return view('front.bingo.show', compact('bingo', 'words'));
    }

    public function generate(string $bingoId)
    {
        return $bingoId;
    }
}