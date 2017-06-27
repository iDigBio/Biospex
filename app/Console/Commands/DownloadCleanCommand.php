<?php

namespace App\Console\Commands;

use App\Exceptions\BiospexException;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use App\Repositories\Contracts\DownloadContract;
use App\Exceptions\Handler;

class DownloadCleanCommand extends Command
{

    /**
     * @var Filesystem
     */
    public $filesystem;

    /**
     * @var DownloadContract
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
     * @var Handler
     */
    private $handler;

    /**
     * DownloadCleanCommand constructor.
     *
     * @param Filesystem $filesystem
     * @param DownloadContract $downloadContract
     * @param Handler $handler
     */
    public function __construct(
        Filesystem $filesystem,
        DownloadContract $downloadContract,
        Handler $handler
    )
    {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->downloadContract = $downloadContract;
        $this->handler = $handler;

        $this->nfnExportDir = config('config.nfnExportDir');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $downloads = $this->downloadContract->setCacheLifetime(0)
            //->where('count', '>', 5)
            ->where('created_at', '<', Carbon::now()->subDays(30), 'or')
            ->findAll();

        $downloads->each(function ($download)
        {
            $file = $this->nfnExportDir . '/' . $download->file;
            if ($this->filesystem->isFile($file))
            {
                echo 'Deleting ' . $download->file;
                //$this->filesystem->delete($file);
            }

            //$this->downloadContract->delete($download->id);
        });
    }
}
