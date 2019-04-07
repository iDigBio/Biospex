<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
     * Create a new job instance.
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

        $tmpDir = \Storage::path('scratch/2-test');


        exec("cd $tmpDir && find -name '*.*' -print >./export.manifest");
        exec("cd $tmpDir && sudo tar -czf ../export.tar.gz --files-from ./export.manifest");
    }
}
