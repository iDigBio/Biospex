<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Console\Commands;

use App\Models\Reconcile;
use App\Services\Csv\Csv;
use App\Services\Subject\SubjectService;
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

    private $fixDir;

    private $birdImages;

    private $birdOccurrences;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        public Csv $csv,
        public SubjectService $subjectService,
        public Reconcile $reconcile)
    {
        parent::__construct();
        $this->fixDir = \Storage::disk('local')->path('fossils/fix/');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->csv->readerCreateFromPath($this->fixDir.'bird-images.csv');
        $this->csv->setHeaderOffset();
        $this->birdImages = $this->csv->getRecords();

        $this->csv->readerCreateFromPath($this->fixDir.'bird-occurrences.csv');
        $this->csv->setHeaderOffset();
        $this->birdOccurrences = $this->csv->getRecords();

        $this->csv->readerCreateFromPath($this->fixDir.'reconcile-374.csv');
        $this->csv->setHeaderOffset();
        $header = $this->csv->getHeader();
        $header[] = 'subject_identifier';
        $reconcileReader = $this->csv->getReader();

        $this->csv->writerCreateFromPath($this->fixDir.'expert-reconcile.csv');
        $this->csv->insertOne($header);

        foreach ($reconcileReader as $offset => $record) {
            $this->fixReconcileWithExpert($record);

            $subject = $this->subjectService->find($record['subject_subjectId']);
            $identifier = $subject['identifier'];

            $birdImage = $this->findBirdImageRecord($identifier);
            if (empty($birdImage)) {
                echo 'Missing bird image '.$identifier.PHP_EOL;

                continue;
            }

            $birdOccurrence = $this->findBirdOccurrenceRecord($birdImage['coreid']);
            if (empty($birdOccurrence)) {
                echo 'Missing bird occurrence '.$birdImage['coreid'].PHP_EOL;

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

    public function fixReconcileWithExpert(&$record)
    {
        $reconciled = $this->reconcile->where('subject_id', $record['subject_id'])->first();
        foreach ($record as $key => $value) {
            $record[$key] = $reconciled[$key] ?? $value;
        }
    }
}
