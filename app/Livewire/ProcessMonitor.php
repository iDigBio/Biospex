<?php

namespace App\Livewire;

use App\Models\ExportQueue;
use App\Models\OcrQueue;
use Livewire\Component;

class ProcessMonitor extends Component
{
    public string $exportHtml = '';

    public string $ocrHtml = '';

    protected $listeners = ['livewire.refresh-process-monitor' => 'render'];

    public function mount()
    {
        $this->loadData();
    }

    public function render()
    {
        $this->loadData();

        return view('livewire.process-monitor');
    }

    private function loadData(): void
    {
        $exportData = $this->getExportData();
        $ocrData = $this->getOcrData();

        $this->exportHtml = $exportData['html'];
        $this->ocrHtml = $ocrData['html'];
    }

    private function getExportData(): array
    {
        $queues = ExportQueue::query()
            ->with(['expedition'])
            ->withCount(['files as processed_files' => fn ($q) => $q->where('processed', 1)])
            ->where('error', 0)
            ->orderBy('id')
            ->get();

        $html = '';
        $count = 0;

        foreach ($queues as $queue) {
            if (! $queue->queued) {
                $position = $count === 0
                    ? t('Next in queue.')
                    : t(n(':count export in queue before processing begins.', ':count exports in queue before processing begins.', $count), [':count' => $count]);

                $html .= view('common.export-process-queued', [
                    'title' => $queue->expedition?->title ?? '—',
                    'remainingCount' => $position,
                ])->render();
            } else {
                $stage = config('zooniverse.export_stages')[$queue->stage] ?? 'Unknown';
                $processedRecords = $queue->stage === 1
                    ? t(' :processed of :total completed.', [
                        ':processed' => $queue->processed_files ?: 0,
                        ':total' => $queue->total,
                    ])
                    : null;

                $html .= view('common.export-process', [
                    'title' => $queue->expedition?->title ?? '—',
                    'stage' => $stage,
                    'processedRecords' => $processedRecords,
                ])->render();
            }
            $count++;
        }

        return ['html' => $html];
    }

    private function getOcrData(): array
    {
        $records = OcrQueue::query()
            ->with(['expedition', 'project'])
            ->withCount(['files as processed_files' => fn ($q) => $q->where('processed', 1)])
            ->orderBy('id')
            ->get();

        $html = '';
        $count = 0;

        foreach ($records as $record) {
            $title = $record->expedition?->title ?? $record->project?->title ?? '—';
            $batches = $count === 0
                ? ''
                : t(n(':batches_queued process remains in queue before processing begins', ':batches_queued processes remain in queue before processing begins', $count), [':batches_queued' => $count]);

            $ocr = t(n(':processed record of :total completed.', ':processed records of :total completed.', $record->processed_files), [
                ':processed' => $record->processed_files,
                ':total' => $record->total,
            ]);

            $html .= view('common.ocr-process', [
                'title' => $title,
                'ocr' => $ocr,
                'batches' => $batches,
            ])->render();

            $count++;
        }

        return ['html' => $html];
    }
}
