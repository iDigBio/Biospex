<?php

namespace App\Repositories;

use App\Repositories\Contracts\FaqCategory;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class FaqCategoryRepository extends Repository implements FaqCategory, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\FaqCategory::class;
    }
}
