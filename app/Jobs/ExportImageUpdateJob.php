<?php

namespace App\Jobs;

use App\Models\ExportQueue;
use App\Models\ExportQueueFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Job to update the status of image export processing and trigger CSV build when complete.
 *
 * This job handles updating individual export queue file statuses and checks if the entire
 * export process is complete to trigger the next stage of processing.
 */
class ExportImageUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  int  $queueId  The ID of the export queue
     * @param  string  $subjectId  The ID of the subject being processed
     * @param  string  $status  The processing status ('success' or 'failed')
     * @param  string|null  $error  Optional error message if processing failed
     */
    public function __construct(
        public int $queueId,
        public string $subjectId,
        public string $status,
        public ?string $error = null
    ) {
        $this->onQueue('config.queue.export');
    }

    /**
     * Execute the job.
     *
     * Updates the processing status of an export queue file and checks if the export is complete.
     */
    public function handle(): void
    {
        $file = ExportQueueFile::where('queue_id', $this->queueId)
            ->where('subject_id', $this->subjectId)
            ->first();

        if (! $file) {
            return;
        }

        if ($this->status === 'success') {
            $file->processed = 1;
        } else {
            $file->processed = 1;
            $file->message = $this->error ?? 'Processing failed';
        }

        $file->save();

        $this->checkIfExportComplete();
    }

    /**
     * Check if all files in the export queue have been processed.
     *
     * If all files are processed and the queue is in stage 1,
     * advances to stage 2 and dispatches the CSV build job.
     */
    private function checkIfExportComplete(): void
    {
        $queue = ExportQueue::find($this->queueId);
        if (! $queue) {
            return;
        }

        $total = $queue->files()->count();
        $processed = $queue->files()->where('processed', 1)->count();

        if ($total === $processed && $queue->stage === 1) {
            $queue->stage = 2;
            $queue->save();

            ZooniverseExportBuildCsvJob::dispatch($queue);
        }
    }
}
