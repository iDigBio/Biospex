<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Jobs\BingoJob;
use App\Repositories\Interfaces\Bingo;
use App\Repositories\Interfaces\Project;
use App\Services\Api\GeoLocation;

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
     * @var \App\Services\Api\GeoLocation
     */
    private $location;

    /**
     * BingosController constructor.
     *
     * @param \App\Repositories\Interfaces\Bingo $bingoContract
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param \App\Services\Api\GeoLocation $location
     */
    public function __construct(Bingo $bingoContract, Project $projectContract, GeoLocation $location)
    {
        $this->bingoContract = $bingoContract;
        $this->projectContract = $projectContract;
        $this->location = $location;
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
        $bingo = $this->bingoContract->find($bingoId);
        if (!$bingo) {
            return __('message.bingo_not_found');
        }
        $this->location->locate('68.63.24.33');

        $attributes = [
            'bingo_id' => $bingo->id,
            'ip' => $this->location->ip
        ];
        $values = [
            'bingo_id' => $bingo->id,
            'ip' => $this->location->ip,
            'latitude' => $this->location->latitude,
            'longitude' => $this->location->longitude,
            'city' => $this->location->city
        ];

        //$bingo->maps()->updateOrCreate($attributes, $values);

        BingoJob::dispatch($bingoId);
        \JavaScript::put([
            'channel' => config('config.poll_bingo_channel') . '.' . $bingoId
        ]);

        return view('front.bingo.card');
    }
}