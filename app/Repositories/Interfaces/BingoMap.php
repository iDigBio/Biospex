<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
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

    /**
     * Retrieve bingo map by bingo id and uuid.
     *
     * @param int $bingoId
     * @param string $uuid
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getBingoMapByBingoIdUuid(int $bingoId, string $uuid): ?Model;
}