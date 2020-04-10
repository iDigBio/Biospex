<?php

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
        $results = $this->model->with(['user', 'project', 'words'])
            ->where('user_id', $userId)->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function createBingo(array $attributes): \Illuminate\Database\Eloquent\Model
    {
        $bingo = $this->create($attributes);

        $words = collect($attributes['words'])->map(function ($word) {
            return new BingoWord($word);
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

            return $result;
        });

        $bingo->words()->saveMany($words->all());

        return $bingo;
    }
}