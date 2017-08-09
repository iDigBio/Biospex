<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\PanoptesTranscriptionContract;
use App\Repositories\Contracts\ProjectContract;
use App\Repositories\Contracts\SubjectContract;
use App\Services\Report\Report;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FixIdentifiersCommand extends Command
{

    public $rejectCount;
    public $eachCount;
    public $uniques;
    public $duplicates;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:identifiers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix identifiers and remove urls';

    /**
     * @var ProjectContract
     */
    private $projectContract;

    /**
     * @var ExpeditionContract
     */
    private $expeditionContract;

    /**
     * @var SubjectContract
     */
    private $subjectContract;

    /**
     * @var Report
     */
    private $report;

    /**
     * @var
     */
    private $deletedRecords;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $uniqueColumns;
    /**
     * @var PanoptesTranscriptionContract
     */
    private $panoptesTranscriptionContract;

    /**
     * Create a new command instance.
     * @param ProjectContract $projectContract
     * @param ExpeditionContract $expeditionContract
     * @param SubjectContract $subjectContract
     * @param PanoptesTranscriptionContract $panoptesTranscriptionContract
     * @param Report $report
     */
    public function __construct(
        ProjectContract $projectContract,
        ExpeditionContract $expeditionContract,
        SubjectContract $subjectContract,
        PanoptesTranscriptionContract $panoptesTranscriptionContract,
        Report $report
    )
    {
        parent::__construct();
        $this->projectContract = $projectContract;
        $this->expeditionContract = $expeditionContract;
        $this->subjectContract = $subjectContract;
        $this->report = $report;

        $this->uniqueColumns = collect(['providerManagedID', 'uuid', 'recordId']);
        $this->panoptesTranscriptionContract = $panoptesTranscriptionContract;
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $this->cleanExistingProject();

        $projects = $this->projectContract->setCacheLifetime(0)
            ->with(['group', 'expeditions'])
            ->orderBy('id')
            ->findAll();

        $projects->each(function ($project) {
            $this->processSubjects($project);
            $this->updateExpeditionStats($project);
            $this->sendReport($project);
        });

        echo 'Complete' . PHP_EOL;
    }

    public function cleanExistingProject()
    {
        $subjects = $this->subjectContract->setCacheLifetime(0)
            ->where('project_id', '=', 17)
            ->where('created_at', '>=', Carbon::create(2017, 07, 26))
            ->findAll();
        $subjects->each(function ($subject) {
            $this->subjectContract->delete($subject->_id);
        });

        echo 'Cleaned project 17';
    }

    public function processSubjects($project)
    {
        echo 'Processing subjects for Project ' . $project->id . PHP_EOL;

        $this->deletedRecords = [];

        $subjects = $this->subjectContract->setCacheLifetime(0)
            ->where('project_id', '=', $project->id)
            ->where(function ($query) {
                $query->where('id', 'like', 'http:%');
                $query->where('id', 'like', '%format%', 'or');
            })
            ->orderBy('created_at', 'asc')
            ->findAll();

        $this->uniques = $subjects->unique('providerManagedID');
        $this->duplicates = $subjects->diffKeys($this->uniques);

        $this->duplicates->reject(function ($duplicate) {
            return $this->deleteDuplicateIfExpeditionEmpty($duplicate);
        })->each(function ($duplicate) {
            $this->checkForTranscriptions($duplicate);
        });

        $this->uniques->each(function($unique){
            $this->saveSubject($unique);
        });
    }

    public function deleteDuplicateIfExpeditionEmpty($duplicate)
    {
        if (count($duplicate->expedition_ids) === 0)
        {
            $this->buildDeletedRecords($duplicate, 'Duplicate not assigned to Expedition.');
            $this->subjectContract->delete($duplicate->_id);

            return true;
        }

        return false;
    }

    public function saveSubject($subject, $isDup = false, $expeditionIds = null)
    {
        if ($expeditionIds !== null)
            $subject->expedition_ids = $expeditionIds;

        $uuid = $this->uuidFromIdentifier($subject->providerManagedID);
        $subject->id = $isDup ? 'dup-' . $uuid : $uuid;

        if (strpos($subject->id, 'dup') !== false){
            echo 'duplicate made' . PHP_EOL;
        }

        $subject->identifier = $subject->accessURI;
        $subject->save();
    }

    protected function updateExpeditionStats($project)
    {
        $project->expeditions->each(function ($expedition) {
            $record = $this->expeditionContract->setCacheLifetime(0)
                ->with('stat')
                ->find($expedition->id);

            $count = $this->expeditionContract->setCacheLifetime(0)
                ->getExpeditionSubjectCounts($expedition->id);

            $record->stat->subject_count = $count;
            $record->stat->transcriptions_total = transcriptions_total($count);
            $record->stat->transcriptions_completed = transcriptions_completed($expedition->id);
            $record->stat->percent_completed = transcriptions_percent_completed($record->stat->transcriptions_total, $record->stat->transcriptions_completed);

            $record->stat->save();
        });
    }

    protected function sendReport($project)
    {
        if (empty($this->deletedRecords))
        {
            echo 'deleted records empty ' . PHP_EOL;
            return;
        }

        $vars = [
            'title'          => 'Deleted subjects for ' . $project->title,
            'message'        => 'Attached is a csv file containing deleted subjects for the existing project. Either the subject was not assigned to an expedition or it was a duplicate of an existing subject that was assigned to an expedition.',
            'groupId'        => $project->group->id,
            'attachmentName' => 'deleted-subjects-' . $project->id . '-'
        ];

        echo 'calling process complete ' . PHP_EOL;
        $this->report->processComplete($vars, $this->deletedRecords);
    }

    protected function buildDeletedRecords($subject, $message)
    {
        $this->deletedRecords[] = [
            'reason'        => $message,
            'subject_id'    => $subject->_id,
            'identifier'    => $subject->identifier,
            'occurrence_id' => $subject->occurrence->id,
            'accessURI'     => $subject->accessURI
        ];
    }

    /**
     * Pull UUID from value.
     *
     * @param value
     * @return mixed
     */
    public function uuidFromIdentifier($value)
    {
        $pattern = '/\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i';
        return preg_match($pattern, $value, $matches) ? $matches[0] : $value;
    }

    /**
     * Duplicates have expeditions ids.
     * @param $duplicate
     */
    public function checkForTranscriptions($duplicate)
    {
        $transcriptionCount = $this->getTranscriptionCount($duplicate);
        $originals = $this->uniques->where('providerManagedID', $duplicate->providerManagedID);
        $originals->each(function ($original, $key) use ($duplicate, $transcriptionCount) {

            $count = $this->getTranscriptionCount($original);

            if (empty($original->expedition_ids))
            {
                unset($this->uniques[$key]);
                $this->buildDeletedRecords($original, 'Original Subject not assigned. Duplicate was assigned to Expedition. Keeping Duplicate');
                $this->subjectContract->delete($original->_id);
                $this->saveSubject($duplicate);

                return;
            }

            if( ! empty($original->expedition_ids))
            {
                // no transcriptions
                // merge expedition ids into original and delete duplicate
                if ($transcriptionCount === 0)
                {
                    $expeditionIds = array_unique(array_merge($original->expedition_ids, $duplicate->expedition_ids));
                    $this->saveSubject($original, false, $expeditionIds);
                    $this->buildDeletedRecords($duplicate, 'No transcriptions. Merge duplicate into original and deleted duplicate. Expeditions . ' . implode(',', $expeditionIds));
                    $this->subjectContract->delete($duplicate->_id);

                    return;
                }

                // no transcriptions in original, transcriptions in duplicate
                // merge expedition ids into duplicate and delete original
                if ($count === 0 && $transcriptionCount > 0)
                {
                    $expeditionIds = array_unique(array_merge($duplicate->expedition_ids, $original->expedition_ids));
                    $this->saveSubject($duplicate, false, $expeditionIds);
                    $this->buildDeletedRecords($original, 'Duplicate has transcriptions. Merge original into duplicate and deleted original. Expeditions . ' . implode(',', $expeditionIds));
                    unset($this->uniques[$key]);
                    $this->subjectContract->delete($original->_id);
                }

                // transcriptions in both original and duplicate
                // create "dup" id
                if ($count > 0 && $transcriptionCount > 0)
                {
                    $this->buildDeletedRecords($duplicate, 'Original and Duplicate has Transcriptions. Creating "dup-" prefix for duplicate id.');
                    $this->saveSubject($duplicate, true);

                    return;
                }

                return;
            }

            return;
        });
    }

    public function getTranscriptionCount($duplicate)
    {
        return $this->panoptesTranscriptionContract->setCacheLifetime(0)
            ->findWhere(['subject_subjectId', '=', $duplicate->_id])
            ->count();
    }
}
