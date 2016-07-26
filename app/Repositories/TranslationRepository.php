<?php 

namespace App\Repositories;

use App\Repositories\Contracts\Translation;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class TranslationRepository extends Repository implements Translation, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Translation::class;
    }
}
