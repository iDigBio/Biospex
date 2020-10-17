<?php
/**
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

namespace App\Services\Process;

use App\Models\User;
use App\Notifications\JobError;
use App\Repositories\Interfaces\Download;
use App\Repositories\Interfaces\Expedition;
use App\Services\Csv\Csv;
use Exception;
use File;
use Illuminate\Support\Carbon;
use Storage;

class ReconcileProcessService
{
    /**
     * @var \App\Repositories\Interfaces\Expedition
     */
    private $expeditionContract;

    /**
     * @var \App\Repositories\Interfaces\Download
     */
    private $downloadContract;

    /**
     * @var string
     */
    private $csvPath;

    /**
     * @var string
     */
    private $recPath;

    /**
     * @var string
     */
    private $tranPath;

    /**
     * @var string
     */
    private $sumPath;

    /**
     * @var string
     */
    private $expPath;

    /**
     * @var string
     */
    private $pythonPath;

    /**
     * @var string
     */
    private $reconcilePath;

    /**
     * @var string
     */
    private $command;

    /**
     * @var \App\Services\Csv\Csv
     */
    private $csvService;

    /**
     * ReconcileProcessService constructor.
     *
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param \App\Repositories\Interfaces\Download $downloadContract
     * @param \App\Services\Csv\Csv $csvService
     */
    public function __construct(Expedition $expeditionContract, Download $downloadContract, Csv $csvService)
    {
        $this->expeditionContract = $expeditionContract;
        $this->downloadContract = $downloadContract;
        $this->csvService = $csvService;
    }

    /**
     * Process expedition through reconcile process.
     *
     * @param $expeditionId
     */
    public function process($expeditionId)
    {
        try {

            $expedition = $this->expeditionContract->findWith($expeditionId, ['panoptesProject']);

            $this->setPaths($expeditionId);

            if (! File::exists($this->csvPath) || ! isset($expedition->panoptesProject)) {
                throw new Exception(t('File does not exist.<br><br>:method<br>:path', [
                    ':method' => __METHOD__,
                    ':path'   => $this->csvPath,
                ]));
            }

            if (! $this->checkFileEmpty()) {
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

            $this->updateOrCreateDownloads($expeditionId);
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
    public function processExplained(\App\Models\Expedition $expedition)
    {
        $this->setPaths($expedition->id);

        if (! File::exists($this->csvPath) || $expedition->nfnActor->pivot->completed === 0) {
            throw new Exception(t('File does not exist.<br><br>:method<br>:path', [
                ':method' => __METHOD__,
                ':path'   => $this->csvPath,
            ]));
        }

        $this->setCommand(true);

        $this->runCommand();

        if (! File::exists($this->expPath)) {
            throw new Exception(t('File does not exist.<br><br>:method<br>:path', [
                ':method' => __METHOD__,
                ':path'   => $this->expPath,
            ]));
        }
    }

    /**
     * Set paths.
     *
     * @param $expeditionId
     */
    protected function setPaths($expeditionId)
    {
        $this->csvPath = Storage::path(config('config.nfn_downloads_classification').'/'.$expeditionId.'.csv');
        $this->recPath = Storage::path(config('config.nfn_downloads_reconcile').'/'.$expeditionId.'.csv');
        $this->tranPath = Storage::path(config('config.nfn_downloads_transcript').'/'.$expeditionId.'.csv');
        $this->sumPath = Storage::path(config('config.nfn_downloads_summary').'/'.$expeditionId.'.html');
        $this->expPath = Storage::path(config('config.nfn_downloads_explained').'/'.$expeditionId.'.csv');

        $this->pythonPath = config('config.python_path');
        $this->reconcilePath = config('config.reconcile_path');
    }

    /**
     * Check if classification file has any rows.
     *
     * @return int
     * @throws \League\Csv\Exception
     */
    protected function checkFileEmpty()
    {
        $this->csvService->readerCreateFromPath($this->csvPath);
        $this->csvService->setDelimiter();
        $this->csvService->setEnclosure();
        $this->csvService->setEscape('"');
        $this->csvService->setHeaderOffset();

        return $this->csvService->getReaderCount(); // false if 0
    }

    /**
     * Check files exist.
     *
     * @return bool
     */
    protected function checkFilesExist()
    {
        if (File::exists($this->csvPath) && File::exists($this->tranPath) && File::exists($this->recPath) && File::exists($this->sumPath)) {
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
     * @return string|void
     */
    protected function setCommand($explained = false)
    {
        if ($explained) {
            $this->command = "{$this->pythonPath} {$this->reconcilePath} --explanations --reconciled {$this->expPath} {$this->csvPath}";

            return;
        }

        $this->command = "{$this->pythonPath} {$this->reconcilePath} --reconciled {$this->recPath} --unreconciled {$this->tranPath} --summary {$this->sumPath} {$this->csvPath}";
    }

    /**
     * Update or create downloads.
     *
     * @param $expeditionId
     * @param bool $explained
     */
    protected function updateOrCreateDownloads($expeditionId, $explained = false)
    {
        collect(config('config.nfn_file_types'))->filter(function ($type) use ($explained) {
            return $explained ? $type === 'reconciled_with_expert_opinion' : $type !== 'reconciled_with_expert_opinion';
        })->each(function ($type) use ($expeditionId) {
            $values = [
                'expedition_id' => $expeditionId,
                'actor_id'      => 2,
                'file'          => $type !== 'summary' ? $expeditionId.'.csv' : $expeditionId.'.html',
                'type'          => $type,
                'updated_at'    => Carbon::now()->format('Y-m-d H:i:s'),
            ];
            $attributes = [
                'expedition_id' => $expeditionId,
                'actor_id'      => 2,
                'file'          => $type !== 'summary' ? $expeditionId.'.csv' : $expeditionId.'.html',
                'type'          => $type,
            ];

            $this->downloadContract->updateOrCreate($attributes, $values);
        });
    }
}