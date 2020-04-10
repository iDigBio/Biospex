<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;
use Illuminate\Support\Collection;

interface Bingo extends RepositoryInterface
{
    /**
     * Get index of bingo games for user.
     *
     * @param $userId
     * @return \Illuminate\Support\Collection
     */
    public function getAdminIndex(int $userId): Collection;

    /**
     * Create a new bingo game.
     *
     * @param array $attributes
     * @return \App\Models\Bingo
     */
    public function createBingo(array $attributes): \Illuminate\Database\Eloquent\Model;

    /**
     * Update bingo game.
     *
     * @param array $attributes
     * @param string $resourceId
     */
    public function updateBingo(array $attributes, string $resourceId);
}