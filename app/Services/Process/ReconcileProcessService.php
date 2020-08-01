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
use Exception;
use File;
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
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $oldReconcile;

    /**
     * @var string
     */
    private $command;

    /**
     * ReconcileProcessService constructor.
     *
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param \App\Repositories\Interfaces\Download $downloadContract
     */
    public function __construct(Expedition $expeditionContract, Download $downloadContract)
    {
        $this->expeditionContract = $expeditionContract;
        $this->downloadContract = $downloadContract;

        $this->oldReconcile = config('config.old_reconcile');
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
                throw new Exception(__('pages.file_does_not_exist_error_msg', [
                    'method' => __METHOD__,
                    'path'   => $this->csvPath,
                ]));
            }

            $this->setCommand();

            $this->runCommand();

            $expeditionIds[] = $expedition->id;

            if (! $this->checkFilesExist()) {
                throw new Exception(__('pages.file_does_not_exist_error_msg', [
                    'method' => __METHOD__,
                    'path'   => $expeditionId,
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
            throw new Exception(__('pages.file_does_not_exist_error_msg', [
                'method' => __METHOD__,
                'path'   => $this->csvPath,
            ]));
        }

        $this->setCommand(true);

        $this->runCommand();

        if (! File::exists($this->expPath)) {
            throw new Exception(__('pages.file_does_not_exist_error_msg', [
                'method' => __METHOD__,
                'path'   => $this->expPath,
            ]));
        }
    }

    /**
     * Set paths.
     *
     * @TODO Remove old reconcile once everything is working before updatinig master branch
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

        $this->pythonPath = $this->oldReconcile ? config('config.old_python_path') : config('config.python_path');
        $this->reconcilePath = $this->oldReconcile ? config('config.old_reconcile_path') : config('config.reconcile_path');
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
     * @TODO Refactor after removing old reconcile process.
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
     * @TODO Simplify after fixing old reconcile to new.
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

        $this->command = $this->oldReconcile ?
            "{$this->pythonPath} {$this->reconcilePath} -r {$this->recPath} -u {$this->tranPath} -s {$this->sumPath} {$this->csvPath}" :
            "{$this->pythonPath} {$this->reconcilePath} --reconciled {$this->recPath} --unreconciled {$this->tranPath} --summary {$this->sumPath} {$this->csvPath}";
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