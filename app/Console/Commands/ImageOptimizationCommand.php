<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ImageOptimizer;

class ImageOptimizationCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'image:opt';

    /**
     * The console command description.
     */
    protected $description = 'Optimize images';

    /**
     * Create a new job instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $dir = public_path('images');
        $files = collect(File::allFiles($dir));
        $files->each(function($file){
            ImageOptimizer::optimize($file->getPathname());
        });

    }
}
