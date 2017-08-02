<?php

namespace App\Services\Model;

use App\Repositories\Contracts\DownloadContract;
use App\Services\File\FileService;

class DownloadService
{

    /**
     * @var DownloadContract
     */
    private $downloadContract;

    /**
     * @var FileService
     */
    public $fileService;

    /**
     * DownloadService constructor.
     * @param DownloadContract $downloadContract
     * @param FileService $fileService
     */
    public function __construct(DownloadContract $downloadContract, FileService $fileService)
    {
        $this->downloadContract = $downloadContract;
        $this->fileService = $fileService;
    }

    /**
     * @param array $downloads
     */
    public function deleteFiles($downloads)
    {
        foreach ($downloads as $download)
        {
            $this->fileService->filesystem->delete(config('config.nfn_export_dir') . '/' . $download->file);
        }
    }

    /**
     * Update or create download.
     *
     * @param $attributes
     * @param $values
     * @return mixed
     */
    public function updateOrCreate($attributes, $values)
    {
        return $this->downloadContract->updateOrCreateDownload($attributes, $values);
    }
}