<?php

namespace App\Console\Commands;

use App\Models\Subject;
use App\Repositories\Contracts\Expedition;
use App\Services\Csv\Csv;
use Illuminate\Console\Command;

class TestAppCommand extends Command
{

    /**
     * The console command name.
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';
    /**
     * @var Csv
     */
    private $csv;
    /**
     * @var Expedition
     */
    private $expedition;

    /**
     * TestAppCommand constructor.
     * @param Csv $csv
     * @param Expedition $expedition
     */
    public function __construct(Csv $csv, Expedition $expedition)
    {
        parent::__construct();
        $this->csv = $csv;
        $this->expedition = $expedition;
    }

    public function fire()
    {
        $ids = [13 => 51]; //, 13 => 53, 17 => 47, 34 => 59];

        foreach ($ids as $projectId => $id)
        {

            $file = $projectId . '-' . $id . '.csv';
            $this->csv->readerCreateFromPath(storage_path($file));
            $rows = $this->csv->fetch();

            $subjectIds = [];
            foreach ($rows as $row)
            {
                $subjectIds[] = $row[0];
            }

            $expedition = $this->expedition->find($id);

            $existingSubjectIds = [];
            foreach ($expedition->subjects as $subject) {
                $existingSubjectIds[] = $subject->_id;
            }

            $subjectModel = new Subject();
            $subjectModel->detachSubjects($existingSubjectIds, $expedition->id);

            $expedition->subjects()->attach($subjectIds);

            $total = transcriptions_total(count($subjectIds));
            $completed = transcriptions_completed($expedition->id);
            $values = [
                'subject_count' => count($subjectIds),
                'transcriptions_total' => $total,
                'transcriptions_completed' => $completed,
                'percent_completed' => transcriptions_percent_completed($total, $completed)
            ];
            $expedition->stat()->updateOrCreate(['expedition_id' => $expedition->id], $values);
        }
    }
}
