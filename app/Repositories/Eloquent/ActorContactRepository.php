<?php 

namespace App\Repositories\Eloquent;

use App\Models\ActorContact;
use App\Repositories\Contracts\ActorContactContract;
use Illuminate\Contracts\Container\Container;

class ActorContactRepository extends EloquentRepository implements ActorContactContract
{

    /**
     * ActorContactRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(ActorContact::class)
            ->setRepositoryId('biospex.repository.actorContact');
    }

}
