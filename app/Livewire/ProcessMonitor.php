<?php

namespace App\Livewire;

use App\Models\ExportQueue;
use App\Models\OcrQueue;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProcessMonitor extends Component
{
    public string $exportHtml = '';

    public string $ocrHtml = '';

    protected $listeners = ['refresh-process-monitor' => 'render'];

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
        $user = Auth::user();
        $groupIds = $user?->groups()->pluck('groups.id')->toArray() ?? [];

        $exportData = $this->getExportData($groupIds);
        $ocrData = $this->getOcrData($groupIds);

        $this->exportHtml = $exportData['html'];
        $this->ocrHtml = $ocrData['html'];
    }

    private function getExportData(array $groupIds): array
    {
        $queues = ExportQueue::query()
            ->with(['expedition.project.group'])
            ->withCount(['files as processed_files' => fn ($q) => $q->where('processed', 1)])
            ->where('error', 0)
            ->orderBy('id')
            ->get();

        $html = '';
        $count = 0;

        foreach ($queues as $queue) {
            $groupId = $queue->expedition?->project?->group?->id;
            if ($groupId && ! in_array($groupId, $groupIds)) {
                continue;
            }

            if ($queue->queued) {
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
                        ':processed' => $queue->processed_files ?: 1,
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

    private function getOcrData(array $groupIds): array
    {
        $records = OcrQueue::query()
            ->with(['expedition', 'project.group'])
            ->withCount(['files as processed_files' => fn ($q) => $q->where('processed', 1)])
            ->orderBy('id')
            ->get();

        $html = '';
        $count = 0;

        foreach ($records as $record) {
            $groupId = $record->project?->group?->id;
            if ($groupId && ! in_array($groupId, $groupIds)) {
                continue;
            }

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

    // Call from jobs for instant updates
    public static function refresh()
    {
        \Livewire\Livewire::dispatch('refresh-process-monitor');
    }
}
