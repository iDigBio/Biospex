<?php

namespace App\Console\Commands;

use Artisan;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class AppRouteListCommand
 */
class AppRouteListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'route:file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send the route list to file instead of console.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $output = new BufferedOutput;
        Artisan::call('route:list --json', [], $output);
        \File::put(storage_path('routes.json'), Artisan::output());
    }
}
