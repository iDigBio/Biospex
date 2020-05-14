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
     * @var string
     */
    private $bingoId;

    /**
     * @var string|null
     */
    private $mapId;

    /**
     * BingoJob constructor.
     *
     * @param string $bingoId
     * @param string|null $mapId
     */
    public function __construct(string $bingoId, string $mapId = null)
    {
        $this->bingoId = $bingoId;
        $this->mapId = $mapId;
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
        $data['markers'] = $locations->map(function($location) {
            return [
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'city' => $location->city
            ];
        })->toArray();

        $data['winner'] = null;
        if ($this->mapId !== null) {
            $map = $bingoMapContract->find($this->mapId);
            $data['winner']['city'] = $map->city;
            $data['winner']['uuid'] = $map->uuid;
        }


        BingoEvent::dispatch($this->bingoId, $data);
    }
}
