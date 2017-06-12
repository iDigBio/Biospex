<?php 

namespace App\Repositories\Eloquent;

use App\Models\Translation;
use App\Repositories\Contracts\TranslationContract;
use Illuminate\Contracts\Container\Container;

class TranslationRepository extends EloquentRepository implements TranslationContract
{

    /**
     * TranslationContractRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Translation::class)
            ->setRepositoryId('biospex.repository.translation');
    }
}
