<?php 

namespace App\Repositories;

use App\Repositories\Contracts\OcrCsv;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class OcrCsvRepository extends Repository implements OcrCsv, CacheableInterface
{

    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\OcrCsv::class;
    }
}


