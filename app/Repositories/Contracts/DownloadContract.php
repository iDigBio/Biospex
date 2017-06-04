<?php

namespace App\Repositories\Contracts;

interface DownloadContract extends RepositoryContract, CacheableContract
{

    /**
     * Update or create download.
     *
     * @param array $attributes
     * @param array $values
     * @return mixed
     */
    public function updateOrCreateDownload(array $attributes = [], array $values = []);
}
