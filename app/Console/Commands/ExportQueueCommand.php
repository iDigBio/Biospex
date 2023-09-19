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

namespace App\Console\Commands;

use App\Models\Expedition;
use App\Repositories\DownloadRepository;
use App\Repositories\ExpeditionRepository;
use App\Repositories\ExportQueueRepository;
use App\Services\Actor\ActorFactory;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Storage;

/**
 * Class ExportQueueCommand
 *
 * @package App\Console\Commands
 */
class ExportQueueCommand extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:queue {expeditionId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fire export queue process. Expedition Id resets the Expedition.';

    /**
     * @var \App\Repositories\ExpeditionRepository
     */
    private ExpeditionRepository $expeditionRepository;

    /**
     * @var \App\Repositories\DownloadRepository
     */
    private DownloadRepository $downloadRepository;

    /**
     * ExportQueueCommand constructor.
     *
     * @param \App\Repositories\ExpeditionRepository $expeditionRepository
     * @param \App\Repositories\DownloadRepository $downloadRepository
     */
    public function __construct(
        ExpeditionRepository $expeditionRepository,
        DownloadRepository $downloadRepository
    ) {
        parent::__construct();
        $this->expeditionRepository = $expeditionRepository;
        $this->downloadRepository = $downloadRepository;
    }

    /**
     * Handle job.
     */
    public function handle(): void
    {
        is_null($this->argument('expeditionId')) ?
            event('exportQueue.check') :
            $this->handleExpeditionExport();
    }

    /**
     * Handles resetting Expedition attributes from command line.
     *
     * @return void
     */
    private function handleExpeditionExport(): void
    {
        $expeditionId = $this->argument('expeditionId');

        $expedition = $this->getExpedition($expeditionId);

        if (!is_null($expedition->exportQueue)) $expedition->exportQueue->delete();

        $this->resetExpeditionData($expedition);
    }

    /**
     * Reset data for expedition when regenerating export.
     *
     * @param \App\Models\Expedition $expedition
     */
    public function resetExpeditionData(Expedition $expedition): void
    {
        $this->deleteExportFiles($expedition->id);

        // Set actor_expedition pivot state to 1 if currently 0.
        // Otherwise, it's a regeneration export and state stays the same
        $attributes = [
            'state' => $expedition->nfnActor->pivot->state === 0 ? 1 : $expedition->nfnActor->pivot->state,
            'total' => $expedition->stat->local_subject_count,
        ];

        $expedition->nfnActor->expeditions()->updateExistingPivot($expedition->id, $attributes);

        // Set state to 1 to handle regenerating exports without effecting database value.
        $expedition->nfnActor->pivot->state = 1;

        ActorFactory::create($expedition->nfnActor->class)->actor($expedition->nfnActor);
    }

    /**
     * Delete existing exports files for expedition.
     *
     * @param string $expeditionId
     */
    public function deleteExportFiles(string $expeditionId): void
    {
        $downloads = $this->downloadRepository->getExportFiles($expeditionId);

        $downloads->each(function ($download) {
            if (Storage::disk('s3')->exists(config('config.export_dir').'/'.$download->file)) {
                Storage::disk('s3')->delete(config('config.export_dir').'/'.$download->file);
            }
            $download->delete();
        });
    }

    /**
     * Get expedition with nfnActor and stat.
     *
     * @param int $expeditionId
     * @return \App\Models\Expedition
     */
    private function getExpedition(int $expeditionId): Expedition
    {
        return $this->expeditionRepository->findWith($expeditionId, ['nfnActor', 'stat', 'exportQueue']);
    }
}