<?php 

namespace App\Repositories\Eloquent;

use App\Models\OcrCsv as Model;
use App\Repositories\Interfaces\OcrCsv;

class OcrCsvRepository extends EloquentRepository implements OcrCsv
{

    /**
     * Specify Model class name
     *
     * @return \Illuminate\Database\Eloquent\Model|string
     */
    public function model()
    {
        return Model::class;
    }
}


