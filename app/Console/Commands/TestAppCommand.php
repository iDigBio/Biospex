<?php

namespace App\Console\Commands;

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
        // try end time - start time / end time - current
        $event = \App\Models\Event::find(1);

        $now = Carbon::now();//->setTimezone('America/New_York');

        $start_date = $event->start_date;//->setTimezone('America/New_York');

        $end_date = $event->end_date;//->setTimezone('America/New_York');

        if ($now < $start_date)
        {
            echo '0%' . PHP_EOL;
        }
        elseif ($now > $end_date)
        {
            echo '100%' . PHP_EOL;
        }
        else
        {
            $startDiff = $now->diffInSeconds($start_date);
            $endDiff = $end_date->diffInSeconds($now);
            echo $startDiff . PHP_EOL;
            echo $endDiff . PHP_EOL;

            echo round(($startDiff / $endDiff) * 100) . PHP_EOL;
        }
    }
}
