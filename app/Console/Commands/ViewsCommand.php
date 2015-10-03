<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ViewsCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'views:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear views folder';

    /**
     * The file system instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        foreach ($this->files->files(storage_path().'/views') as $file) {
            $this->files->delete($file);
        }

        $this->info('Views deleted from cache');
    }
}
