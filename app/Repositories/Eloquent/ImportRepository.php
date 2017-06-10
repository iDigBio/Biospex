<?php

namespace App\Repositories\Eloquent;


use App\Models\Import;
use App\Repositories\Contracts\ImportContract;
use Illuminate\Contracts\Container\Container;

class ImportRepository extends EloquentRepository implements ImportContract
{
    /**
     * ActorRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Import::class)
            ->setRepositoryId('biospex.repository.import');
    }

}