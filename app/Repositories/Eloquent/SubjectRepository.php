<?php

namespace App\Repositories\Eloquent;

use App\Models\Subject;
use App\Repositories\Contracts\SubjectContract;
use App\Repositories\Traits\EloquentRepositoryCommon;
use Illuminate\Contracts\Container\Container;

class SubjectRepository extends EloquentRepository implements SubjectContract
{

    use EloquentRepositoryCommon;

    /**
     * SubjectRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Subject::class)
            ->setRepositoryId('biospex.repository.subject');
    }
}