<?php

namespace App\Services\Actor;

use App\Repositories\Contracts\Download;
use App\Repositories\Contracts\Expedition;

class ActorRepositoryService
{

    /**
     * @var Expedition
     */
    public $expedition;

    /**
     * @var Download
     */
    public $download;

    /**
     * ActorServiceRepositories constructor.
     *
     * @param Expedition $expedition
     * @param Download $download
     */
    public function __construct(Expedition $expedition, Download $download)
    {
        $this->expedition = $expedition;
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
            $values = [
                'expedition_id' => $recordId,
                'actor_id'      => $actorId,
                'file'          => pathinfo($file, PATHINFO_BASENAME),
            ];
            $this->createDownload($values);
        }
    }

    /**
     * Created download.
     *
     * @param $values
     * @return mixed
     */
    public function createDownload($values)
    {
        return $this->download->create($values);
    }
}