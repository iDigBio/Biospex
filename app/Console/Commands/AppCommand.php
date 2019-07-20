<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * AppCommand constructor.
     */
    public function __construct(Filesystem $filesystem) {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $workingDirectory = config('config.nfn_downloads_classification');
        $workingDirectoryPath = \Storage::path($workingDirectory);

        $files = collect($this->filesystem->files($workingDirectoryPath));
        $files->each(function ($file) {
            $fileName = $this->filesystem->name($file);
            echo $fileName . PHP_EOL;
        });
    }
}