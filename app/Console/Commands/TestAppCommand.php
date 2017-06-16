<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\DownloadContract;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;


class TestAppCommand extends Command
{

    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';


    /**
     * TestAppCommand constructor.
     */
    public function __construct(

    )
    {
        parent::__construct();
    }

    /**
     *
     */
    public function handle(DownloadContract $downloadContract)
    {
        $files = \File::allFiles(config('config.nfn_export_dir'));

        foreach ($files as $file)
        {
            $baseName = \File::basename($file);
            $count = $downloadContract->findWhere(['file', '=', $baseName]);
            echo $baseName . ' ' . $count . PHP_EOL;
        }
    }
}
