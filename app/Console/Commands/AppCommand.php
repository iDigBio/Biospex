<?php

namespace App\Console\Commands;

use App\Models\OcrQueue;
use File;
use Illuminate\Console\Command;
use Storage;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Repositories\Interfaces\OcrQueue
     */
    private $ocrQueue;

    /**
     * @var \App\Repositories\Interfaces\OcrFile
     */
    private $ocrFile;

    /**
     * AppCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $queue = OcrQueue::get()->first();
        $folderPath = $this->createDir($queue);
        dd($folderPath);

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

        if (! Storage::exists($folderPath)) {
            echo "Making Directory" . PHP_EOL;
            try {
                Storage::makeDirectory($folderPath);
            }
            catch(\Exception $exception) {
                dd($exception);
            }
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
