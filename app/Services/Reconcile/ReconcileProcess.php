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

namespace App\Services\Reconcile;

use App\Models\Expedition;
use App\Models\User;
use App\Notifications\JobError;
use App\Repositories\DownloadRepository;
use App\Repositories\ExpeditionRepository;
use App\Services\Csv\Csv;
use Exception;
use File;
use Illuminate\Support\Carbon;
use Storage;

/**
 * Class ReconcileProcess
 *
 * @package App\Services\Process
 */
class ReconcileProcess
{
    /**
     * @var \App\Repositories\ExpeditionRepository
     */
    private ExpeditionRepository $expeditionRepo;

    /**
     * @var \App\Repositories\DownloadRepository
     */
    private DownloadRepository $downloadRepo;

    /**
     * @var \App\Services\Csv\Csv
     */
    private Csv $csv;

    /**
     * @var string
     */
    private string $csvPath;

    /**
     * @var string
     */
    private string $expPath;

    /**
     * @var string
     */
    private string $pythonPath;

    /**
     * @var string
     */
    private string $reconcilePath;

    /**
     * @var string
     */
    private string $csvFullPath;

    /**
     * @var string
     */
    private string $recFullPath;

    /**
     * @var string
     */
    private string $tranFullPath;

    /**
     * @var string
     */
    private string $sumFullPath;

    /**
     * @var string
     */
    private string $expFullPath;

    /**
     * @var string
     */
    private string $command;

    /**
     * ReconcileProcess constructor.
     *
     * @param \App\Repositories\ExpeditionRepository $expeditionRepo
     * @param \App\Repositories\DownloadRepository $downloadRepo
     * @param \App\Services\Csv\Csv $csv
     */
    public function __construct(ExpeditionRepository $expeditionRepo, DownloadRepository $downloadRepo, Csv $csv)
    {
        $this->expeditionRepo = $expeditionRepo;
        $this->downloadRepo = $downloadRepo;
        $this->csv = $csv;
    }

    /**
     * Process expedition through reconcile process.
     *
     * @param $expeditionId
     */
    public function process($expeditionId): void
    {
        try {

            $expedition = $this->expeditionRepo->findWith($expeditionId, ['panoptesProject']);

            $this->setPaths($expeditionId);

            Storage::disk('efs')->put($this->csvPath, Storage::disk('s3')->get($this->csvPath));

            if (! File::exists($this->csvFullPath) || ! isset($expedition->panoptesProject)) {
                echo 'file does not exist' . PHP_EOL;
                throw new Exception(t('File does not exist.<br><br>:method<br>:path', [
                    ':method' => __METHOD__,
                    ':path'   => $this->csvFullPath
                ]));
            }

            if (! $this->checkCsvEmpty()) {
                File::delete($this->csvFullPath);
                return;
            }

            $this->setCommand();

            $this->runCommand();

            if (! $this->checkFilesExist()) {
                throw new Exception(t('File does not exist.<br><br>:method<br>:path', [
                    ':method' => __METHOD__,
                    ':path'   => $expeditionId,
                ]));
            }

            $this->uploadFileToS3('classification', $this->csvFullPath, $expedition->id);
            $this->uploadFileToS3('reconcile', $this->recFullPath, $expedition->id);
            $this->uploadFileToS3('transcript', $this->tranFullPath, $expedition->id);
            $this->uploadFileToS3('summary', $this->sumFullPath, $expedition->id);

            $this->updateOrCreateDownloads($expeditionId);

            $this->cleanDirs();

        } catch (Exception $e) {
            $user = User::find(1);
            $message = [
                'Message:'.$e->getFile().': '.$e->getLine().' - '.$e->getMessage(),
            ];
            $user->notify(new JobError(__FILE__, $message));
        }
    }

    /**
     * Process reconcile explained file.
     *
     * @param \App\Models\Expedition $expedition
     * @throws \Exception
     */
    public function processExplained(Expedition $expedition)
    {
        $this->setPaths($expedition->id);

        Storage::disk('efs')->put($this->csvPath, Storage::disk('s3')->get($this->csvPath));

        // TODO This starts the expert reconciliation. The Nfn actor should be in a completed state
        if (! File::exists($this->csvFullPath) || $expedition->nfnActor->pivot->state !== 3) {
            throw new Exception(t('File does not exist.<br><br>:method<br>:path', [
                ':method' => __METHOD__,
                ':path'   => $this->csvFullPath
            ]));
        }

        $this->setCommand(true);

        $this->runCommand();

        if (! File::exists($this->expFullPath)) {
            throw new Exception(t('File does not exist.<br><br>:method<br>:path', [
                ':method' => __METHOD__,
                ':path'   => $this->expPath,
            ]));
        }

        $this->uploadFileToS3('explained', $this->expFullPath, $expedition->id);

        $this->cleanDirs();
    }

    /**
     * Set paths.
     *
     * @param $expeditionId
     */
    protected function setPaths($expeditionId)
    {
        $this->csvPath = config('config.zooniverse.directory.classification').'/'.$expeditionId.'.csv';
        $this->csvFullPath = Storage::disk('efs')->path($this->csvPath);

        $this->recFullPath = Storage::disk('efs')->path(config('config.zooniverse.directory.reconcile').'/'.$expeditionId.'.csv');
        $this->tranFullPath = Storage::disk('efs')->path(config('config.zooniverse.directory.transcript').'/'.$expeditionId.'.csv');
        $this->sumFullPath = Storage::disk('efs')->path(config('config.zooniverse.directory.summary').'/'.$expeditionId.'.html');

        $this->expPath = config('config.zooniverse.directory.explained').'/'.$expeditionId.'.csv';
        $this->expFullPath = Storage::disk('efs')->path($this->expPath);

        $this->pythonPath = config('config.python_path');
        $this->reconcilePath = config('config.reconcile_path');
    }

    /**
     * Check if classification file has any rows.
     *
     * @return int
     * @throws \League\Csv\Exception
     */
    protected function checkCsvEmpty(): int
    {
        $this->csv->readerCreateFromPath($this->csvFullPath);
        $this->csv->setDelimiter();
        $this->csv->setEnclosure();
        $this->csv->setEscape('"');
        $this->csv->setHeaderOffset();

        return $this->csv->getReaderCount(); // false if 0
    }

    /**
     * Check files exist.
     *
     * @return bool
     */
    protected function checkFilesExist(): bool
    {
        if (File::exists($this->csvFullPath) &&
            File::exists($this->tranFullPath) &&
            File::exists($this->recFullPath) &&
            File::exists($this->sumFullPath))
        {
            return true;
        }

        return false;
    }

    /**
     * Run reconcile command.
     *
     * @throws \Exception
     */
    protected function runCommand()
    {
        exec($this->command, $output, $return);

        if ($return) {
            $message = 'Error processing reconcile command: '.$this->command;
            throw new Exception($message);
        }
    }

    /**
     * Set command string.
     *
     * @param bool $explained
     * @return void
     */
    protected function setCommand(bool $explained = false)
    {
        if ($explained) {
            $this->command = "{$this->pythonPath} {$this->reconcilePath} --reconciled {$this->expFullPath} --explanations {$this->csvFullPath}";

            return;
        }

        $this->command = "{$this->pythonPath} {$this->reconcilePath} --reconciled {$this->recFullPath} --unreconciled {$this->tranFullPath} --summary {$this->sumFullPath} {$this->csvFullPath}";
    }

    /**
     * Update or create downloads.
     *
     * @param $expeditionId
     */
    protected function updateOrCreateDownloads($expeditionId): void
    {
        collect(config('config.zooniverse.file_types'))->each(function ($type) use ($expeditionId) {
            $values = [
                'expedition_id' => $expeditionId,
                'actor_id'      => config('config.zooniverse.actor_id'),
                'file'          => $type !== 'summary' ? $expeditionId.'.csv' : $expeditionId.'.html',
                'type'          => $type,
                'updated_at'    => Carbon::now()->format('Y-m-d H:i:s'),
            ];
            $attributes = [
                'expedition_id' => $expeditionId,
                'actor_id'      => config('config.zooniverse.actor_id'),
                'file'          => $type !== 'summary' ? $expeditionId.'.csv' : $expeditionId.'.html',
                'type'          => $type,
            ];

            $this->downloadRepo->updateOrCreate($attributes, $values);
        });
    }

    /**
     * Upload efs file to s3.
     *
     * @param string $dir
     * @param string $efsFullPath
     * @param string $fileName
     * @return void
     */
    protected function uploadFileToS3(string $dir, string $efsFullPath, string $fileName)
    {
        $s3Dir = config('config.zooniverse.directory.' . $dir);
        $ext = $dir !== 'summary' ? '.csv' : '.html';
        Storage::disk('s3')->putFileAs($s3Dir, $efsFullPath, $fileName.$ext);
    }

    /**
     * Clean all directories.
     *
     * @return void
     */
    protected function cleanDirs()
    {

        File::cleanDirectory(Storage::disk('efs')->path(config('config.zooniverse.directory.classification')));
        File::cleanDirectory(Storage::disk('efs')->path(config('config.zooniverse.directory.reconcile')));
        File::cleanDirectory(Storage::disk('efs')->path(config('config.zooniverse.directory.reconciled')));
        File::cleanDirectory(Storage::disk('efs')->path(config('config.zooniverse.directory.transcript')));
        File::cleanDirectory(Storage::disk('efs')->path(config('config.zooniverse.directory.summary')));
        File::cleanDirectory(Storage::disk('efs')->path(config('config.zooniverse.directory.explained')));
    }
}