<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use App\Interfaces\Download;

class DownloadCleanCommand extends Command
{

    /**
     * @var Filesystem
     */
    public $filesystem;

    /**
     * @var Download
     */
    public $downloadContract;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'download:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Remove expired download files.";

    /**
     * Directory where nfn downloads are stored.
     *
     * @var string
     */
    protected $nfnExportDir;

    /**
     * DownloadCleanCommand constructor.
     *
     * @param Filesystem $filesystem
     * @param Download $downloadContract
     */
    public function __construct(
        Filesystem $filesystem,
        Download $downloadContract
    )
    {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->downloadContract = $downloadContract;

        $this->nfnExportDir = config('config.nfn_export_dir');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $downloads = $this->downloadContract->getDownloadsForCleaning();

        $downloads->each(function ($download)
        {
            $file = $this->nfnExportDir . '/' . $download->file;
            if ($this->filesystem->isFile($file))
            {
                echo 'Deleting ' . $file . PHP_EOL;
                $this->filesystem->delete($file);
            }

            $this->downloadContract->delete($download->id);
        });

        $files = collect($this->filesystem->files($this->nfnExportDir));
        $files->each(function($file){
            $fileName = $this->filesystem->basename($file);
            $result = $this->downloadContract->findBy('file', $fileName);
            if ( ! $result)
            {
                echo 'Deleting ' . $file . PHP_EOL;
                $this->filesystem->delete($file);
            }
        });

    }
}
