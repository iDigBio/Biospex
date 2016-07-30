<?php 

namespace App\Repositories;

use App\Repositories\Contracts\NfnClassification;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class NfnClassificationRepository extends Repository implements NfnClassification, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\NfnClassification::class;
    }
}
