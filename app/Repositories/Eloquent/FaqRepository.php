<?php

namespace App\Repositories\Eloquent;

use App\Models\Faq;
use App\Repositories\Contracts\FaqContract;
use Illuminate\Contracts\Container\Container;

class FaqRepository extends EloquentRepository implements FaqContract
{

    /**
     * FaqRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Faq::class)
            ->setRepositoryId('biospex.repository.faq');
    }
}
