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
}