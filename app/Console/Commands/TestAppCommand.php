<?php

namespace App\Console\Commands;

use App\Exceptions\BiospexException;
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
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     */
    public function handle()
    {
        $iNum1 = 10;
        $iNum2 = 0;

        try{

            $iResult = $iNum1 / $iNum2;
            echo "Division Result of \$iNum1 and $iNum2 = ".($iResult).PHP_EOL;
        }
        catch (\App\Exceptions\BiospexRuntimeException $e){
            echo "Division by Zero is not possible" . PHP_EOL;
        }
    }
}
