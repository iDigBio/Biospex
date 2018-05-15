<?php

namespace App\Console\Commands;

use App\Models\Event;
use Carbon\Carbon;
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
        $user = 'testone';
        $events = Event::with(['groups.users' => function($query) use($user){
            $query->where('nfn_user', $user);
        }])->where('project_id', 13)->get();

        $events->filter(function($event){
            $currentTime = Carbon::now($event->timezone);
            $currentTime->between($event->start);
            $first = Carbon::create(2012, 9, 5, 1);
            $second = Carbon::create(2012, 9, 5, 5);
            var_dump(Carbon::create(2012, 9, 5, 3)->between($first, $second));
        });

        dd($events->count());
    }
}
