<?php 

namespace App\Repositories\Eloquent;

use App\Models\Resource;
use App\Repositories\Contracts\ResourceContract;
use Illuminate\Contracts\Container\Container;

class ResourceRepository extends EloquentRepository implements ResourceContract
{

    /**
     * ResourceRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Resource::class)
            ->setRepositoryId('biospex.repository.resource');
    }
}
