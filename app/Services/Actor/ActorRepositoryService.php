<?php

namespace App\Services\Actor;

use App\Repositories\Contracts\Download;
use App\Repositories\Contracts\ExpeditionContract;

class ActorRepositoryService
{

    /**
     * @var ExpeditionContract
     */
    public $expeditionContract;

    /**
     * @var Download
     */
    public $download;

    /**
     * ActorServiceRepositories constructor.
     *
     * @param ExpeditionContract $expeditionContract
     * @param Download $download
     */
    public function __construct(ExpeditionContract $expeditionContract, Download $download)
    {
        $this->expedition = $expeditionContract;
        $this->download = $download;
    }

    /**
     * Add download files to downloads table.
     *
     * @param $recordId
     * @param $actorId
     * @param array $files
     */
    public function createDownloads($recordId, $actorId, array $files)
    {
        foreach ($files as $file)
        {
            $attributes = [
                'expedition_id' => $recordId,
                'actor_id'      => $actorId,
                'file'          => pathinfo($file, PATHINFO_BASENAME),
            ];

            $values = [
                'expedition_id' => $recordId,
                'actor_id'      => $actorId,
                'file'          => pathinfo($file, PATHINFO_BASENAME),
            ];
            $this->createDownload($attributes, $values);
        }
    }

    /**
     * Created download.
     *
     * @param $attributes
     * @param $values
     * @return mixed
     */
    public function createDownload($attributes, $values)
    {
        return $this->download->updateOrCreate($attributes, $values);
    }
}