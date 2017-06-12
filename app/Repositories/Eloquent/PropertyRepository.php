<?php 

namespace App\Repositories\Eloquent;

use App\Models\Property;
use App\Repositories\Contracts\PropertyContract;
use Illuminate\Contracts\Container\Container;

class PropertyRepository extends EloquentRepository implements PropertyContract
{

    /**
     * PropertyRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Property::class)
            ->setRepositoryId('biospex.repository.property');
    }
}
