<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReportCleanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleans exports/reports directory';

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
     *
     * @return mixed
     */
    public function handle()
    {
        \File::cleanDirectory(config('config.export_reports_dir'));
    }
}
