<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\JobError;
use App\Notifications\RecordDeleteComplete;
use App\Services\Model\SubjectService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteUnassignedSubjectsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private $projectId;

    /**
     * @var \App\Models\User
     */
    private $user;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\User $user
     * @param int $projectId
     */
    public function __construct(User $user, int $projectId)
    {
        $this->user = $user;
        $this->projectId = $projectId;
        $this->onQueue(config('config.default_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Model\SubjectService $subjectService
     * @return void
     */
    public function handle(SubjectService $subjectService)
    {
        try {
            $subjectService->deleteUnassignedByProject($this->projectId);

            $message = [
                t('All unassigned subjects for project id %s have been deleted.', $this->projectId)
            ];

            $this->user->notify(new RecordDeleteComplete($message));

            $this->delete();
        }
        catch (Exception $e) {
            $message = [
                'Error: ' . t('Could not delete unassigned subjects for project %s', $this->projectId),
                'Message:' . $e->getFile() . ': ' . $e->getLine() . ' - ' . $e->getMessage()
            ];
            $this->user->notify(new JobError(__FILE__, $message));

            $this->delete();
        }
    }
}
