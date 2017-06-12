<?php

namespace App\Repositories\Eloquent;

use App\Models\Header;
use App\Repositories\Contracts\HeaderContract;
use Illuminate\Contracts\Container\Container;

class HeaderRepository extends EloquentRepository implements HeaderContract
{

    /**
     * HeaderRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Header::class)
            ->setRepositoryId('biospex.repository.header');
    }
    
}
