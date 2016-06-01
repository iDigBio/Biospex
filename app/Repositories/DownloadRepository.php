<?php 

namespace App\Repositories;

use App\Repositories\Contracts\Download;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class DownloadRepository extends Repository implements Download, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Download::class;
    }
}
