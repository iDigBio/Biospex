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
        $exportDir = \Storage::path('exports');
        $tmpDir = \Storage::path('scratch/2-test');

        //find . -name '*.txt' -print >/tmp/test.manifest
        //tar -cvzf textfiles.tar.gz --files-from /tmp/test.manifest
        //find . -name '*.txt' | xargs rm -v

        exec("cd $tmpDir && find -name '*.*' -print >../export.manifest");
        exec("cd $tmpDir && sudo tar -czfv ../export.tar.gz --files-from ../export.manifest", $out, $ok);
        //exec("cd $exportDir && sudo tar -czf export.tar.gz --files-from $tmpDir/export.manifest", $out, $ok);

    }
}
