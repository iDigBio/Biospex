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

namespace App\Services\Models;

use App\Models\Bingo;
use Illuminate\Support\Collection;

readonly class BingoModelService
{
    public function __construct(private Bingo $bingo, private BingoWordModelService $bingoWordModelService) {}

    public function findBingoWithRelations(int $id, array $relations = []): mixed
    {
        return $this->bingo->with($relations)->find($id);
    }

    /**
     * Get bingo with relations by user id.
     */
    public function getBingoByUserIdWithRelations(int $userId, array $relations = []): Collection
    {
        return $this->bingo->with($relations)->where('user_id', $userId)->get();
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

            return $this->bingoWordModelService->makeBingoWord($values);
        });

        $bingo->words()->saveMany($words->all());

        return $bingo;
    }

    /**
     * Update bingo.
     */
    public function updateBingo(array $attributes, string $resourceId): Bingo
    {
        $bingo = $this->bingo->find($resourceId);
        $bingo->fill($attributes)->save();

        $words = collect($attributes['words'])->map(function ($word) {
            $result = $this->bingoWordModelService->findBingoWordWithRelations($word['id']);
            $result->word = $word['word'];
            $result->definition = $word['definition'];

            return $result;
        });

        $bingo->words()->saveMany($words->all());

        return $bingo;
    }
}
