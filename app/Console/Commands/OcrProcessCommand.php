<?php

namespace App\Console\Commands;

use App\Jobs\OcrTesseractJob;
use App\Models\User;
use App\Repositories\Interfaces\OcrFile;
use App\Repositories\Interfaces\OcrQueue;
use App\Notifications\JobError;
use App\Services\Actor\Ocr\OcrComplete;
use Artisan;
use Exception;
use File;
use Illuminate\Console\Command;
use Storage;

class OcrProcessCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocrprocess:records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Polls Ocr server for file status and fires polling event';

    /**
     * @var OcrQueue
     */
    private $ocrQueueContract;

    /**
     * @var OcrComplete
     */
    private $ocrComplete;

    /**
     * @var \App\Repositories\Interfaces\OcrFile
     */
    private $ocrFileContract;

    /**
     * OcrProcessCommand constructor.
     *
     * OcrProcessCommand constructor.
     *
     * @param OcrQueue $ocrQueueContract
     * @param \App\Repositories\Interfaces\OcrFile $ocrFileContract
     * @param OcrComplete $ocrComplete
     */
    public function __construct(
        OcrQueue $ocrQueueContract,
        OcrFile $ocrFileContract,
        OcrComplete $ocrComplete
    ) {
        parent::__construct();

        $this->ocrQueueContract = $ocrQueueContract;
        $this->ocrFileContract = $ocrFileContract;
        $this->ocrComplete = $ocrComplete;
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $queue = $this->ocrQueueContract->getOcrQueueForOcrProcessCommand();

        if ($queue === null) {
            return;
        }

        try {

            $folderPath = $this->createDir($queue);
            $files = $this->ocrFileContract->getAllOcrQueueFiles($queue->id);

            if ($queue->status === 1) {
                $this->ocrComplete->process($queue, $files);
                $this->deleteDir($folderPath);
                Artisan::call('ocrprocess:records');

                return;
            }

            $files->reject(function ($file) {
                return $file->status === 1 && !empty($file->ocr);
            })->each(function ($file) use ($queue, $folderPath) {
                OcrTesseractJob::dispatch($queue, $file, $folderPath);
            });

            return;
        } catch (Exception $e) {
            event('ocr.error', $queue);

            $messages = [
                $queue->project->title,
                'Error processing ocr record '.$queue->id,
                'File: '.$e->getFile(),
                'Message: '.$e->getMessage(),
                'Line: '.$e->getLine(),
            ];

            $user = User::find(1);
            $user->notify(new JobError(__FILE__, $messages));
        }
    }

    /**
     * Create directory for queue.
     *
     * @param $queue
     * @return string
     */
    private function createDir($queue)
    {
        $folderPath = 'ocr/' . md5($queue->id);

        if (! File::exists($folderPath)) {
            Storage::makeDirectory($folderPath);
        }

        return $folderPath;
    }

    /**
     * Delete directory for queue.
     *
     * @param $folderPath
     */
    private function deleteDir($folderPath)
    {
        Storage::deleteDirectory($folderPath);
    }
}
