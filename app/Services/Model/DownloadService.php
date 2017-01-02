<?php

namespace App\Services\Model;


use App\Repositories\Contracts\Download;
use App\Services\File\FileService;


class DownloadService
{

    /**
     * @var Download
     */
    private $download;

    /**
     * @var FileService
     */
    public $fileService;

    /**
     * DownloadService constructor.
     * @param Download $download
     * @param FileService $fileService
     */
    public function __construct(Download $download, FileService $fileService)
    {
        $this->download = $download;
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
}