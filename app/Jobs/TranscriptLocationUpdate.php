<?php

namespace App\Jobs;

use App\Facades\GeneralHelper;
use App\Models\Expedition;
use App\Repositories\Interfaces\StateCounty;
use App\Repositories\Interfaces\Subject;
use App\Repositories\Interfaces\TranscriptionLocation;
use App\Services\MongoDbService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

ini_set('memory_limit', '1024M');

class TranscriptLocationUpdate implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 7200;

    /**
     * @var array
     */
    private $expedition;

    /**
     * @var \App\Repositories\Interfaces\StateCounty|\Illuminate\Foundation\Application
     */
    private $stateCounty;

    /**
     * @var \App\Repositories\Interfaces\TranscriptionLocation|\Illuminate\Foundation\Application
     */
    private $transcriptionLocation;

    /**
     * @var \App\Services\MongoDbService|\Illuminate\Foundation\Application
     */
    private $service;

    /**
     * @var \App\Repositories\Interfaces\Subject|\Illuminate\Foundation\Application
     */
    private $subjectContract;

    /**
     * @var \Illuminate\Config\Repository
     */
    private $dwcTranscriptFields;

    /**
     * @var \Illuminate\Config\Repository
     */
    private $dwcOccurrenceFields;

    /**
     * TranscriptLocationUpdate constructor.
     *
     * @param \App\Models\Expedition $expedition
     */
    public function __construct(Expedition $expedition)
    {
        $this->onQueue(config('config.classification_tube'));
        $this->expedition = $expedition;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->stateCounty = app(StateCounty::class);
        $this->transcriptionLocation = app(TranscriptionLocation::class);
        $this->service = app(MongoDbService::class);
        $this->subjectContract = app(Subject::class);
        $this->dwcTranscriptFields = $fields = config('config.dwcTranscriptFields');
        $this->dwcOccurrenceFields = $fields = config('config.dwcOccurrenceFields');

        $transcriptions = $this->getTranscriptions($this->expedition);
        $this->processTranscriptions($transcriptions);
    }

    /**
     * Get Transcriptions using expedition id
     *
     * @return mixed
     */
    private function getTranscriptions()
    {
        $this->service->setCollection('panoptes_transcriptions');
        $transcriptions = $this->service->find(['subject_expeditionId' => $this->expedition->id]);

        return $transcriptions;
    }

    /**
     * @param $transcriptions
     */
    private function processTranscriptions($transcriptions)
    {
        foreach ($transcriptions as $transcription) {

            $data = [];

            $subject = $this->getSubject($transcription);
            if ($subject === null) {
                continue;
            }

            $this->setDwcLocalityFields($transcription, $subject, $data);

            if (array_key_exists('state_province', $data) && strtolower($data['state_province']) === 'district of columbia') {
                $data['county'] = $data['state_province'];
            }

            if (! $this->checkRequiredStateCounty($data)) {
                continue;
            }

            $this->prepCounty($data);
            $stateAbbr = GeneralHelper::getState($data['state_province']);
            $stateResult = $this->stateCounty->findByCountyState($data['county'], $stateAbbr);

            if ($stateResult === null) {
                continue;
            }

            $values['classification_id'] = $transcription['classification_id'];
            $values['project_id'] = $subject->project_id;
            $values['expedition_id'] = $transcription['subject_expeditionId'];
            $values['state_county_id'] = $stateResult->id;
            $attributes = ['classification_id' => $transcription['classification_id']];

            $this->transcriptionLocation->updateOrCreate($attributes, $values);

        }
    }

    /**
     * Get subject from db to set projectId
     *
     * @param $transcription
     * @return mixed
     */
    private function getSubject($transcription)
    {
        return $this->subjectContract->find(trim($transcription['subject_subjectId']));
    }

    /**
     * Check locality fields from transcription.
     *
     * @param $transcription
     * @param $subject
     * @param $data
     * @return array
     */
    private function setDwcLocalityFields($transcription, $subject, &$data): array
    {
        $this->setDwcLocalityFromTranscript($transcription, $data);
        $this->setDwcLocalityFromOccurrence($subject, $data);

        return $data;

    }

    /**
     * Set the dwc locality fields using transcript.
     *
     * @param $transcription
     * @param $data
     */
    private function setDwcLocalityFromTranscript($transcription, &$data)
    {
        foreach ($this->dwcTranscriptFields as $transcriptField => $mapField) {
            if (isset($transcription[$transcriptField]) && ! empty($transcription[$transcriptField])) {
                $data[$mapField] = $transcription[$transcriptField];

                continue;
            }
        }
    }

    /**
     * Set the dwc locality fields using occurrence.
     *
     * @param $subject
     * @param $data
     */
    private function setDwcLocalityFromOccurrence($subject, &$data)
    {
        if (count($data) == 2) {
            return;
        }

        foreach ($this->dwcOccurrenceFields as $occurrenceField => $mapField) {
            if (isset($subject->occurrence->{$occurrenceField}) && ! empty($subject->occurrence->{$occurrenceField})) {
                $data[$mapField] = $subject->occurrence->{$occurrenceField};

                continue;
            }
        }
    }

    /**
     * Check if state and county exist.
     *
     * @param $data
     * @return bool
     */
    private function checkRequiredStateCounty($data)
    {
        if (! isset($data['state_province']) || ! isset($data['county'])) {
            return false;
        }

        if (empty($data['state_province']) || empty($data['county'])) {
            return false;
        }

        return true;
    }

    /**
     * Prep County for searching database.
     *
     * @param $data
     */
    private function prepCounty(&$data)
    {
        $county = trim(preg_replace("/[^ \w-]/", "", $data['county']));
        $search = ['Saint', 'Sainte', 'Miami Dade', 'De Soto', 'De Kalb', 'county', 'City', 'Not Shown'];
        $replace = ['St.', 'Ste.', 'Miami-Dade', 'DeSoto', 'DeKalb', '', '', ''];
        $county = trim(str_ireplace($search, $replace, $county));
        $data['county'] = $county;
    }
}
