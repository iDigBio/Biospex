<?php

namespace App\Console\Commands;

use App\Services\Csv\Csv;
use App\Services\Model\SubjectService;
use Illuminate\Console\Command;

class ClayUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clay:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Used to update clays BRIT projects';

    /**
     * @var \App\Services\Model\SubjectService
     */
    private $subjectService;

    /**
     * @var \App\Services\Csv\Csv
     */
    private $csv;

    /**
     * Create a new command instance.
     *
     * @param \App\Services\Model\SubjectService $subjectService
     * @param \App\Services\Csv\Csv $csv
     */
    public function __construct(SubjectService $subjectService, Csv $csv)
    {
        parent::__construct();
        $this->subjectService = $subjectService;
        $this->csv = $csv;
    }

    /**
     * Execute the console command.
     *
     * @throws \League\Csv\Exception
     */
    public function handle()
    {
        try {
            $this->csv->readerCreateFromPath(\Storage::path('BRITflags.csv'));
            $this->csv->setDelimiter();
            $this->csv->setEnclosure();
            $this->csv->setHeaderOffset();
            $records = $this->csv->getRecords();

            $missing = [];

            $count = 0;
            foreach ($records as $record) {
                $subject = $this->subjectService->find($record['_id']);
                $subjectArray = $subject->getAttributes();

                if (!isset($subjectArray['occurrence']['preparations'])) {
                    $missing[] = $record['_id'];
                }

                $subjectArray['occurrence']['preparations'] = $record['new_preparations'];

                $this->subjectService->update($subjectArray, $record['_id']);

                $count++;
            }

            echo sprintf('Completed %s records', $count) . PHP_EOL;
            print_r($missing) . PHP_EOL;
        } catch (\Exception $exception) {
            echo $exception->getMessage() . PHP_EOL;
        }
    }
}
