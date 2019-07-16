<?php

namespace App\Console\Commands;

use App\Models\FailedJob;
use App\Models\User;
use App\Notifications\FailedJobReport;
use Illuminate\Console\Command;

class ReportFailedCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'report:failed';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * AppCommand constructor.
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
        $count = FailedJob::count();
        if ($count === 0) {
            return;
        }

        $user = User::find(1);
        $message = __('messages.failed_jobs', ['count' => $count]);
        $user->notify(new FailedJobReport($message));
    }
}