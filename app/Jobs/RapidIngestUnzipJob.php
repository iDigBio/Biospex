<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\JobErrorNotification;
use App\Services\RapidIngestService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Storage;

class RapidIngestUnzipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\User
     */
    private $user;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var bool
     */
    private $update;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\User $user
     * @param string $filePath
     * @param bool $update
     */
    public function __construct(User $user, string $filePath, bool $update = true)
    {
        $this->user = $user;
        $this->filePath = $filePath;
        $this->update = $update;
        $this->onQueue(config('config.rapid_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\RapidIngestService $rapidIngestService
     * @return void
     */
    public function handle(RapidIngestService $rapidIngestService)
    {
        try {
            if (! Storage::exists($this->filePath)) {
                throw new Exception(t('Rapid import zip file does not exist while processing update job.'));
            }

            $fileName = $rapidIngestService->unzipFile($this->filePath);
            $filePath = $rapidIngestService->getImportsPath() . '/' . $fileName;

            if (is_null($fileName) || !Storage::exists($filePath)) {
                throw new Exception(t('CSV file could not be extracted from zip file.'));
            }

            $this->update ? RapidUpdateJob::dispatch($this->user, $filePath, $fileName)
                : RapidImportJob::dispatch($this->user, $filePath, $fileName);

            $this->delete();

        } catch (Exception $e) {
            $attributes = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];

            $this->user->notify(new JobErrorNotification($attributes));

            $this->delete();
        }
    }
}
