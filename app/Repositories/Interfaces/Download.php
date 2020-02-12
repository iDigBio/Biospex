<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;
use Illuminate\Support\Collection;

interface Download extends RepositoryInterface
{
    /**
     * Get downloads for over night cleaning.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getDownloadsForCleaning(): Collection;

    /**
     * Get export files for expedition.
     *
     * @param string $expeditionId
     * @return \Illuminate\Support\Collection
     */
    public function getExportFiles(string $expeditionId): Collection;
}
