<?php

namespace App\Console\Commands;

use App\Jobs\ExpertReviewMigrateReconcilesJob;
use App\Jobs\ExpertReviewProcessExplainedJob;
use App\Jobs\ExpertReviewSetProblemsJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class ExpertReviewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expert:review {expeditionId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Throwable
     */
    public function handle()
    {
        $expeditionId = $this->argument('expeditionId');

        Bus::batch([
            new ExpertReviewProcessExplainedJob($expeditionId),
            new ExpertReviewMigrateReconcilesJob($expeditionId),
            new ExpertReviewSetProblemsJob($expeditionId)
        ])->name('Expert Reconcile ' . $expeditionId)->onQueue(config('config.queues.reconcile'))->dispatch();
    }
}
