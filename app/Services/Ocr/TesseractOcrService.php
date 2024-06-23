<?php
/*
 * Copyright (C) 2015  Biospex
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
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Ocr;

use App\Models\OcrQueue;
use App\Models\Subject;
use App\Notifications\Generic;
use App\Notifications\Traits\ButtonTrait;
use App\Repositories\OcrQueueFileRepository;
use App\Repositories\OcrQueueRepository;
use App\Repositories\SubjectRepository;
use App\Services\Process\CreateReportService;
use Artisan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Str;

/**
 * Class OcrService
 *
 * @package App\Services\Process
 */
class TesseractOcrService
{
    use ButtonTrait;

    /**
     * @var \App\Repositories\OcrQueueRepository
     */
    private OcrQueueRepository $ocrQueueRepo;

    /**
     * @var \App\Repositories\SubjectRepository
     */
    private SubjectRepository $subjectRepo;

    /**
     * @var \App\Services\Process\CreateReportService
     */
    private CreateReportService $createReportService;

    /**
     * @var \App\Repositories\OcrQueueFileRepository
     */
    private OcrQueueFileRepository $ocrQueueFileRepo;

    /**
     * Ocr constructor.
     *
     * @param \App\Repositories\SubjectRepository $subjectRepo
     * @param \App\Repositories\OcrQueueRepository $ocrQueueRepo
     * @param \App\Repositories\OcrQueueFileRepository $ocrQueueFileRepo
     * @param \App\Services\Process\CreateReportService $createReportService
     */
    public function __construct(
        SubjectRepository $subjectRepo,
        OcrQueueRepository $ocrQueueRepo,
        OcrQueueFileRepository $ocrQueueFileRepo,
        CreateReportService $createReportService
    ) {
        $this->ocrQueueRepo = $ocrQueueRepo;
        $this->subjectRepo = $subjectRepo;
        $this->createReportService = $createReportService;
        $this->ocrQueueFileRepo = $ocrQueueFileRepo;
    }

    /**
     * Process ocr payload.
     *
     * @param array $payload
     * @return void
     * @see \App\Listeners\TesseractOcrListener
     */
    public function process(array $payload): void
    {
        $requestPayload = $payload['requestPayload'];
        $responsePayload = $payload['responsePayload'];

        // If errorMessage, something really went bad with lambda function.
        isset($responsePayload['errorMessage']) ?
            $this->handleErrorMessage($requestPayload, $responsePayload['errorMessage']) :
            $this->handleResponse($responsePayload['body']);
    }

    /**
     * Handle error message.
     * $requestPayload['id'] is the ocr_queue_files id.
     *
     * @param array $requestPayload
     * @param string $errorMessage
     * @return void
     */
    public function handleErrorMessage(array $requestPayload, string $errorMessage): void
    {
        $file = $this->ocrQueueFileRepo->find($requestPayload['id']);
        $file->message = $errorMessage;
        $file->processed = true;
        $file->save();

        $this->updateOcrQueue($requestPayload['queue_id']);
    }

    /**
     * Handle response for success or failure.
     * $body['id'] is the ocr_queue_files id.
     *
     * @param array $body
     * @return void
     */
    public function handleResponse(array $body): void
    {
        $this->updateOcrQueueFile($body['id'], $body['message']);
        $this->updateOcrQueue($body['queue_id']);
    }

    /**
     * Return ocr queue for command process.
     *
     * @param bool $reset
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null
     */
    public function getFirstQueue(bool $reset = false): Model|Builder|null
    {
        return $this->ocrQueueRepo->getFirstQueue($reset);
    }

    /**
     * Create ocr queue record.
     *
     * @param int $projectId
     * @param int|null $expeditionId
     * @param array $data
     * @return \App\Models\OcrQueue
     */
    public function createOcrQueue(int $projectId, int $expeditionId = null, array $data = []): OcrQueue
    {
        return $this->ocrQueueRepo->firstOrCreate([
            'project_id'    => $projectId,
            'expedition_id' => $expeditionId,
        ], $data);
    }

    /**
     * Create ocr queue files.
     *
     * @param int $queueId
     * @param int $projectId
     * @param int|null $expeditionId
     */
    public function createOcrQueueFiles(int $queueId, int $projectId, int $expeditionId = null): void
    {
        $query = $this->getSubjectQueryForOcr($projectId, $expeditionId);

        $query->chunk(500, function ($chunk) use ($queueId) {
            $chunk->each(function ($subject) use ($queueId) {
                $attributes = [
                    'queue_id'   => $queueId,
                    'subject_id' => (string) $subject->_id,
                    'access_uri' => $subject->accessURI,
                ];

                $this->ocrQueueFileRepo->firstOrCreate($attributes, $attributes);
            });
        });
    }

    /**
     * Get unprocessed ocr queue files.
     * Limited return depending on config.
     *
     * @param int $queueId
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function getUnprocessedOcrQueueFiles(int $queueId): \Illuminate\Database\Eloquent\Collection|array
    {
        return $this->ocrQueueFileRepo->getUnprocessedOcrQueueFiles($queueId);
    }

    /**
     * Return query for processing subjects in ocr.
     *
     * @param int $projectId
     * @param int|null $expeditionId
     *
     * @return \App\Models\Subject|\Illuminate\Database\Eloquent\Builder
     */
    public function getSubjectQueryForOcr(int $projectId, int $expeditionId = null): Builder|Subject
    {
        return $this->subjectRepo->getSubjectQueryForOcr($projectId, $expeditionId);
    }

    /**
     * Get subject count for ocr process.
     *
     * @param int $projectId
     * @param int|null $expeditionId
     * @return int
     */
    public function getSubjectCountForOcr(int $projectId, int $expeditionId = null): int
    {
        return $this->subjectRepo->getSubjectCountForOcr($projectId, $expeditionId);
    }

    /**
     * Ocr process completed.
     *
     * @param \App\Models\OcrQueue $ocrQueue
     * @return void
     * @throws \League\Csv\CannotInsertRecord
     */
    public function ocrCompleted(OcrQueue $ocrQueue): void
    {
        $this->updateSubjects($ocrQueue->id);

        $this->sendNotify($ocrQueue);

        $ocrQueue->delete();
    }

    /**
     * Update subjects with ocr result.
     *
     * @param int $queueId
     * @return void
     */
    public function updateSubjects(int $queueId): void
    {
        $query = $this->ocrQueueFileRepo->getOcrQueueFileQuery($queueId);

        $query->chunk(500, function ($chunk) use ($queueId) {
            $chunk->each(function ($file) use ($queueId) {
                $this->subjectRepo->update(['ocr' => $file->message], $file->subject_id);
            });
        });
    }

    /**
     * Send notification for completed ocr process.
     *
     * @param \App\Models\OcrQueue $queue
     * @return void
     * @throws \League\Csv\CannotInsertRecord
     */
    public function sendNotify(OcrQueue $queue): void
    {
        $queue->load('project.group.owner');

        $files = $this->ocrQueueFileRepo->getFilesWithError($queue->id);

        $subjects = $files->map(function ($file) {
            return [
                'subject_id' => $file->subject_id,
                'url'        => $file->access_uri,
                'ocr'        => $file->message,
            ];
        });

        $csvName = Str::random().'.csv';
        $fileName = $this->createReportService->createCsvReport($csvName, $subjects->toArray());
        $button = [];
        if ($fileName !== null) {
            $route = route('admin.downloads.report', ['file' => $fileName]);
            $button = $this->createButton($route, t('View OCR Errors'), 'error');
        }

        $attributes = [
            'subject' => t('Ocr Process Complete'),
            'html'    => [
                t('The OCR processing of your data is complete for %s.', $queue->project->title),
            ],
            'buttons' => $button,
        ];

        $queue->project->group->owner->notify(new Generic($attributes));
    }

    /**
     * Update ocr queue file with result.
     *
     * @param string $ocrQueueFileId
     * @param string $message
     * @return void
     */
    private function updateOcrQueueFile(string $ocrQueueFileId, string $message = ''): void
    {
        $attributes = [
            'processed' => true,
            'message'   => trim(preg_replace('/\s+/', ' ', trim($message))),
        ];
        $this->ocrQueueFileRepo->update($attributes, $ocrQueueFileId);
    }

    /**
     * Update queue processed number.
     *
     * @param string $queueId
     * @return void
     */
    private function updateOcrQueue(string $queueId): void
    {
        $this->ocrQueueRepo->find($queueId)->increment('processed');
    }
}