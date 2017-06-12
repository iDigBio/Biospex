<?php 

namespace App\Repositories\Eloquent;

use App\Models\OcrCsv;
use App\Repositories\Contracts\OcrCsvContract;
use Illuminate\Contracts\Container\Container;

class OcrCsvRepository extends EloquentRepository implements OcrCsvContract
{

    /**
     * OcrCsvRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(OcrCsv::class)
            ->setRepositoryId('biospex.repository.ocrCsv');
    }
}


