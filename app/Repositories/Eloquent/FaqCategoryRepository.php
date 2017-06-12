<?php

namespace App\Repositories\Eloquent;

use App\Models\FaqCategory;
use App\Repositories\Contracts\FaqCategoryContract;
use Illuminate\Contracts\Container\Container;

class FaqCategoryRepository extends EloquentRepository implements FaqCategoryContract
{

    /**
     * FaqCategoryRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(FaqCategory::class)
            ->setRepositoryId('biospex.repository.faqCategory');
    }
}
