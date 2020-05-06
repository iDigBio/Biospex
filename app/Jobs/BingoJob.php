<?php

namespace App\Jobs;

use App\Events\BingoEvent;
use App\Repositories\Interfaces\BingoMap;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BingoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private $bingoId;

    /**
     * ScoreBoardJob constructor.
     *
     * @param $bingoId
     */
    public function __construct($bingoId)
    {
        $this->bingoId = $bingoId;
        $this->onQueue(config('config.default_tube'));
    }

    /**
     * Job handle.
     *
     * @param \App\Repositories\Interfaces\BingoMap $bingoMapContract
     */
    public function handle(BingoMap $bingoMapContract)
    {
        $locations = $bingoMapContract->getBingoMapsByBingoId($this->bingoId);
        $data = $locations->map(function($location) {
            return [
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'city' => $location->city
            ];
        });


        BingoEvent::dispatch($this->bingoId, $data->toJson());
    }
}
