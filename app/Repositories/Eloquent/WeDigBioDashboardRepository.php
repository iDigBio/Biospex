<?php

namespace App\Repositories\Eloquent;

use App\Models\WeDigBioDashboard;
use App\Repositories\Contracts\WeDigBioDashboardContract;
use Illuminate\Contracts\Container\Container;

class WeDigBioDashboardRepository extends EloquentRepository implements WeDigBioDashboardContract
{

    /**
     * PanoptesTranscriptionRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(WeDigBioDashboard::class)
            ->setRepositoryId('biospex.repository.wedigbioDashboard');

    }
}