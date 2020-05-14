<?php declare(strict_types = 1);

namespace App\Services\Model;

use App\Repositories\Interfaces\Bingo;
use App\Repositories\Interfaces\Project;
use App\Services\Api\GeoLocation;
use GeneralHelper;
use Illuminate\Support\Collection;
use Session;

class BingoService
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
     * BingoService constructor.
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
     * Get all bingo games.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllBingos(): Collection
    {
        return $this->bingoContract->allWith(['user']);
    }

    /**
     * Find bingo resource.
     *
     * @param string $id
     * @param array $with
     * @return \App\Models\Bingo
     */
    public function findBingoWith(string $id, array $with = []): \App\Models\Bingo
    {
        return $this->bingoContract->findWith($id, $with);
    }

    /**
     * Show bingo page.
     *
     * @param string $bingoId
     * @return array
     */
    public function showBingo(string $bingoId): array
    {
        $bingo = $this->findBingoWith($bingoId, ['words', 'user']);

        $words = $bingo->words->chunk(3);

        return [$bingo, $words];
    }

    /**
     * Generate bingo card.
     *
     * @param \App\Models\Bingo $bingo
     * @return \Illuminate\Support\Collection
     */
    public function generateBingoCard(\App\Models\Bingo $bingo): Collection
    {
        // temp since working local
        $this->location->locate('68.63.24.33');

        $uuid = GeneralHelper::uuidToBin(Session::get('bingoUuid')) ?? null;

        $attributes = [
            'bingo_id' => $bingo->id,
            'uuid' => $uuid,
            'ip' => $this->location->ip
        ];
        $values = [
            'bingo_id' => $bingo->id,
            'uuid' => $uuid,
            'ip' => $this->location->ip,
            'latitude' => $this->location->latitude,
            'longitude' => $this->location->longitude,
            'city' => $this->location->city
        ];

        $map = $bingo->maps()->firstOrCreate($attributes, $values);
        Session::put('bingoUuid', $map->uuid);

        \JavaScript::put([
            'channel' => config('config.poll_bingo_channel') . '.' . $bingo->id,
            'winnerUrl' => route('ajax.get.bingoWinner', ['bingo' => 1, 'map' => $map->id]),
            'mapUuid' => $map->uuid
        ]);

        $i = 1;
        $bingoWords = $bingo->words->pluck('word')->shuffle();
        $bingoWords->splice(12, 0, ['logo']);
        $words = $bingoWords;

        return $words->chunk(5)->map(function($row) use (&$i) {
            $collection = collect(['a'.$i, 'b'.$i, 'c'.$i, 'd'.$i, 'e'.$i]);
            $i++;
            return $collection->combine($row);
        });
    }
}