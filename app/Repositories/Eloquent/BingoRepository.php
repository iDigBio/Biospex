<?php
/**
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
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Repositories\Eloquent;

use App\Models\Bingo as Model;
use App\Models\BingoWord;
use App\Repositories\Interfaces\Bingo;
use Illuminate\Support\Collection;

class BingoRepository extends EloquentRepository implements Bingo
{

    /**
     * Specify Model class name
     *
     * @return \Illuminate\Database\Eloquent\Model|string
     */
    public function model()
    {
        return Model::class;
    }

    /**
     * @inheritDoc
     */
    public function getAdminIndex(int $userId): Collection
    {
        return $this->model->with(['user', 'project', 'words'])
            ->where('user_id', $userId)->get();
    }

    /**
     * @inheritDoc
     */
    public function createBingo(array $attributes): \Illuminate\Database\Eloquent\Model
    {
        $bingo = $this->create($attributes);

        $words = collect($attributes['words'])->map(function ($word) {
            $values = [
                'word' => $word['word'],
                'definition' => $word['definition']
            ];
            return new BingoWord($values);
        });

        $bingo->words()->saveMany($words->all());

        return $bingo;
    }

    /**
     * @inheritDoc
     */
    public function updateBingo(array $attributes, string $resourceId)
    {
        $bingo = $this->update($attributes, $resourceId);

        $words = collect($attributes['words'])->map(function ($word) {
            $result = BingoWord::find($word['id']);
            $result->word = $word['word'];
            $result->definition = $word['definition'];

            return $result;
        });

        $bingo->words()->saveMany($words->all());

        return $bingo;
    }
}