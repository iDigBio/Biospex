<?php 

namespace App\Repositories\Eloquent;

use App\Models\Invite;
use App\Repositories\Contracts\InviteContract;
use Illuminate\Contracts\Container\Container;

class InviteRepository extends EloquentRepository implements InviteContract
{

    /**
     * InviteRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Invite::class)
            ->setRepositoryId('biospex.repository.invite');
    }
}
