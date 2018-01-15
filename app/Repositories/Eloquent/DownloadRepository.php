<?php

namespace App\Repositories\Eloquent;

use App\Models\Download as Model;
use App\Repositories\Interfaces\Download;
use Illuminate\Support\Carbon;

class DownloadRepository extends EloquentRepository implements Download
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

    /**
     * @inheritdoc
     */
    public function getDownloadsForCleaning()
    {
        $results = $this->model
            ->where('type', '=', 'export')
            ->where('created_at', '<', Carbon::now()->subDays(90))
            ->get();

        $this->resetModel();

        return $results;
    }
}