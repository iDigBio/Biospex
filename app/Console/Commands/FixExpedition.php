<?php

namespace App\Console\Commands;

use App\Services\Csv\Csv;
use App\Services\Model\SubjectService;
use Illuminate\Console\Command;

class FixExpedition extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temp command to fix Catherine reconcile files';

    /**
     * @var \App\Services\Csv\Csv
     */
    private Csv $csv;

    /**
     * @var \App\Services\Model\SubjectService
     */
    private SubjectService $subjectService;

    private $fixDir;
    
    private $birdImages;
    
    private $birdOccurrences;
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Csv $csv, SubjectService $subjectService)
    {
        parent::__construct();
        $this->csv = $csv;
        $this->subjectService = $subjectService;
        $this->fixDir = \Storage::disk('local')->path('fossils/fix/');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->csv->readerCreateFromPath($this->fixDir . 'bird-images.csv');
        $this->csv->setHeaderOffset();
        $this->birdImages = $this->csv->getRecords();

        $this->csv->readerCreateFromPath($this->fixDir . 'bird-occurrences.csv');
        $this->csv->setHeaderOffset();
        $this->birdOccurrences = $this->csv->getRecords();

        $this->csv->readerCreateFromPath($this->fixDir . 'reconciled_with_expert_opinion-374.csv');
        $this->csv->setHeaderOffset();
        $header = $this->csv->getHeader();
        $header[] = 'subject_identifier';
        $reconcileReader = $this->csv->getReader();

        $this->csv->writerCreateFromPath($this->fixDir . 'expert-reconcile.csv');
        $this->csv->insertOne($header);

        foreach ($reconcileReader as $offset => $record) {
            $subject = $this->subjectService->find($record['subject_subjectId']);
            $identifier = $subject['identifier'];

            $birdImage = $this->findBirdImageRecord($identifier);
            if (empty($birdImage)) {
                echo 'Missing bird image ' . $identifier . PHP_EOL;
                continue;
            }

            $birdOccurrence = $this->findBirdOccurrenceRecord($birdImage['coreid']);
            if (empty($birdOccurrence)) {
                echo 'Missing bird occurrence ' . $birdImage['coreid'] . PHP_EOL;
                continue;
            }

            $record['subject_catalogNumber'] = $birdOccurrence['catalogNumber'];
            $record['subject_scientificName'] = $birdOccurrence['scientificName'];
            $record['subject_identifier'] = $birdImage['identifier'];

            $this->csv->insertOne($record);
        }
    }

    /**
     * Find bird image record.
     *
     * @param $identifier
     * @return mixed|void
     */
    private function findBirdImageRecord($identifier)
    {
        foreach ($this->birdImages as $birdImage) {
            if ($birdImage['identifier'] === $identifier) {
                return $birdImage;
            }
        }
    }

    /**
     * Search for bird occurrence.
     *
     * @param $coreid
     * @return mixed|void
     */
    private function findBirdOccurrenceRecord($coreid)
    {
        foreach ($this->birdOccurrences as $birdOccurrence) {
            if ($birdOccurrence['id'] === $coreid) {
                return $birdOccurrence;
            }
        }
    }
}
