<?php

namespace App\Jobs;

use App\Repositories\Contracts\Expedition;
use App\Repositories\Contracts\Notification;
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
     * @param Expedition $expedition
     * @param Notification $notification
     * @return void
     */
    public function handle(Expedition $expedition, Notification $notification)
    {
        $this->nfnWorkflowNotification($expedition, $notification);
    }

    /**
     * Build missing nfn workflow notifications.
     *
     * @param $expedition
     * @param $notification
     */
    private function nfnWorkflowNotification($expedition, $notification)
    {
        $results = $expedition->skipCache()->with(['project.group'])->whereHas('workflowManager', ['stopped' => 0])->doesntHave('nfnWorkflow')->get();
        if (null === $results)
        {
            return;
        }

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
