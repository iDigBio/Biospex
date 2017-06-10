<?php

namespace App\Jobs;

use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\NotificationContract;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificationsJob extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     *
     * @param ExpeditionContract $expeditionContract
     * @param NotificationContract $notification
     * @return void
     */
    public function handle(ExpeditionContract $expeditionContract, NotificationContract $notification)
    {
        $notification->truncate();

        $results = $expeditionContract->setCacheLifetime(0)
            ->with(['project.group'])
            ->whereHas('workflowManager', function($query) {
                $query->where('stopped', 0);
            })
            ->whereHas('nfnWorkflow', function($query){
                $query->whereNull('workflow');
            })
            ->findAll();

        foreach ($results as $result)
        {
            $values = [
                'user_id' => $result->project->group->user_id,
                'title' => trans('notifications.notification_workflow_title'),
                'message' => trans('notifications.notification_workflow_message', ['title' => $result->title]),
            ];

            $notification->create($values);
        }
    }
}
