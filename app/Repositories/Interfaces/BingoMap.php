<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;
use Illuminate\Support\Collection;

interface BingoMap extends RepositoryInterface
{
    /**
     * Retrieve bingo map locations.
     *
     * @param string $bingoId
     * @return \Illuminate\Support\Collection
     */
    public function getBingoMapsByBingoId(string $bingoId): Collection;
}