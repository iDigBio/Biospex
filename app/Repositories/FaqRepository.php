<?php

namespace App\Repositories;

use App\Repositories\Contracts\Faq;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class FaqRepository extends Repository implements Faq, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Faq::class;
    }
}
