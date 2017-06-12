<?php 

namespace App\Repositories\Eloquent;

use App\Models\Meta;
use App\Repositories\Contracts\MetaContract;
use Illuminate\Contracts\Container\Container;

class MetaRepository extends EloquentRepository implements MetaContract
{

    /**
     * MetaRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Meta::class)
            ->setRepositoryId('biospex.repository.meta');
    }
}
