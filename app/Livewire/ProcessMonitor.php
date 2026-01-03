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

namespace App\Livewire;

use App\Models\ExportQueue;
use App\Models\OcrQueue;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

/**
 * ProcessMonitor Livewire Component
 *
 * Monitors OCR and Export queue processes and displays their current status.
 */
class ProcessMonitor extends Component
{
    /**
     * Render the process monitor component
     */
    public function render(): \Illuminate\View\View
    {
        return view('livewire.process-monitor', [
            'ocrQueues' => $this->getOcrQueues(),
            'exportQueues' => $this->getExportQueues(),
        ]);
    }

    public function refreshData()
    {
        // Empty method. Livewire will re-render and fetch fresh data.
    }

    /**
     * Get OCR queue records with related expedition and project data
     *
     * @return Collection<OcrQueue>
     */
    private function getOcrQueues(): Collection
    {
        return OcrQueue::query()
            ->select(['id', 'expedition_id', 'project_id', 'total', 'stage'])
            ->with(['expedition:id,title', 'project:id,title'])
            ->withCount(['files as processed_files' => fn ($q) => $q->where('processed', 1)])
            ->orderBy('id')
            ->get();
    }

    /**
     * Get export queue records with related expedition data
     *
     * @return Collection<ExportQueue>
     */
    private function getExportQueues(): Collection
    {
        $queues = ExportQueue::query()
            ->select(['id', 'expedition_id', 'queued', 'stage', 'total'])
            ->with('expedition:id,title')
            ->withCount(['files as processed_files' => fn ($q) => $q->where('processed', 1)])
            ->where('error', 0)
            ->orderBy('id')
            ->get();

        return $queues;
    }
}
