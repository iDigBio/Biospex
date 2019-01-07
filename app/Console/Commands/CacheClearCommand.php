<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CacheClearCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'clear:cache';

    /**
     * The console command description.
     */
    protected $description = 'Clear all caches';

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
        exec('php artisan optimize');
        exec('php artisan cache:clear');
        exec('php artisan route:cache');
        exec('php artisan view:clear');
        exec('php artisan config:cache');
    }
}