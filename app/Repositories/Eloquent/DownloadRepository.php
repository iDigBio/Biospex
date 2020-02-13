<?php

namespace App\Repositories\Eloquent;

use App\Models\Download as Model;
use App\Repositories\Interfaces\Download;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

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
    public function getDownloadsForCleaning(): Collection
    {
        $results = $this->model
            ->where('type', 'export')
            ->where('created_at', '<', Carbon::now()->subDays(90))
            ->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getExportFiles(string $expeditionId): Collection
    {
        $results = $this->model
            ->where('expedition_id', $expeditionId)
            ->where('type', 'export')
            ->get();

        $this->resetModel();

        return $results;
    }
}