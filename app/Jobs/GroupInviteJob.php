<?php

namespace App\Jobs;

use Illuminate\Contracts\Bus\SelfHandling;

class GroupInviteJob extends Job implements SelfHandling
{
    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the job.
     *
     * @param  GroupInviteJob  $job
     * @return void
     */
    public function handle(GroupInviteJob $job)
    {
        //
    }
}
