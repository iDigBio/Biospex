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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Bingo;

use App\Models\Bingo;
use App\Models\BingoUser;
use App\Models\BingoWord;
use App\Models\User;
use App\Services\Api\GeoPlugin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Session;

/**
 * Class BingoService
 */
class BingoService
{
    /**
     * BingoService constructor.
     */
    public function __construct(
        protected Bingo $bingo,
        protected BingoUser $bingoUser,
        protected BingoWord $bingoWord,
        protected GeoPlugin $location
    ) {}

    /**
     * Get bingo with relations by user id.
     */
    public function getAdminIndex(User $user): Collection
    {
        return $user->isAdmin() ?
            $this->bingo->with(['user', 'project', 'words'])->orderBy('created_at', 'desc')->get() :
            $this->bingo->with(['user', 'project', 'words'])->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get bingo with relations for front index.
     */
    public function getFrontIndex(): Collection
    {
        return $this->bingo->with(['user', 'project'])->get()->sortByDesc('created_at');
    }

    /**
     * Create bingo.
     */
    public function createBingo(array $attributes): mixed
    {
        $bingo = $this->bingo->create($attributes);

        $words = collect($attributes['words'])->map(function ($word) {
            $values = [
                'word' => $word['word'],
                'definition' => $word['definition'],
            ];

            return $this->bingoWord->make($values);
        });

        $bingo->words()->saveMany($words->all());

        return $bingo;
    }

    /**
     * Update bingo.
     */
    public function updateBingo(Bingo $bingo, array $attributes): Bingo
    {
        $bingo->fill($attributes)->save();

        collect($attributes['words'])->each(function ($word) {
            $record = $this->bingoWord->find($word['id']);
            $record->word = $word['word'];
            $record->definition = $word['definition'];
            $record->save();
        });

        return $bingo;
    }

    /**
     * Create bingo map. Default Tallahassee if lat/long empty.
     */
    private function createBingoUser(Bingo $bingo): Model|BingoUser
    {
        $this->location->locate();

        $values = [
            'bingo_id' => $bingo->id,
            'ip' => $this->location->ip,
            'latitude' => $this->location->latitude == null ? '30.43826' : $this->location->latitude,
            'longitude' => $this->location->longitude == null ? '-84.28073' : $this->location->longitude,
            'city' => $this->location->city == null ? 'Tallahassee' : $this->location->city,
        ];

        return $bingo->maps()->create($values);
    }

    /**
     * Show bingo page.
     */
    public function showPublicBingo(Bingo &$bingo): Collection
    {
        $bingo->load(['words', 'user', 'project']);

        return $bingo->words->chunk(3);
    }

    /**
     * Generate bingo card.
     */
    public function generateBingoCard(Bingo $bingo): Collection
    {
        $words = $bingo->words->pluck('definition', 'word')->shuffleWords();
        $words->splice(12, 0, [['logo', '']]);

        $i = 1;

        return $words->chunk(5)->map(function ($row) use (&$i) {
            $collection = collect(['a'.$i, 'b'.$i, 'c'.$i, 'd'.$i, 'e'.$i]);
            $i++;

            return $collection->combine($row)->toArray();
        });
    }

    /**
     * Find bingo map by uuid.
     */
    public function getOrCreateBingoUser(Bingo $bingo): BingoUser
    {
        if (Session::get('bingoUserUuid') === null) {
            $bingoUser = $this->createBingoUser($bingo);
            Session::put('bingoUserUuid', $bingoUser->uuid);

            return $bingoUser;
        }

        $bingoUser = $this->bingoUser->where('uuid', Session::get('bingoUserUuid'))->first();
        if ($bingoUser === null) {
            $bingoUser = $this->createBingoUser($bingo);
            Session::put('bingoUserUuid', $bingoUser->uuid);
        }

        return $bingoUser;
    }

    /**
     * Get locations for bingo map.
     */
    public function getMapLocations(int $bingoId): Collection
    {
        return $this->bingoUser->where('bingo_id', $bingoId)->groupBy('city')->get();
    }

    /**
     * Get bingo map markers.
     */
    public function getBingoUserData(Bingo $bingo): string
    {
        $locations = $this->getMapLocations($bingo->id);

        return $locations->map(function ($location) {
            return [
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'city' => $location->city,
            ];
        })->toJson(JSON_NUMERIC_CHECK);
    }
}
