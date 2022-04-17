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

namespace App\Jobs;

use App\Models\ExportQueue as Model;
use App\Notifications\NfnExportError;
use App\Repositories\ExportQueueRepository;
use App\Services\Actor\ActorFactory;
use Artisan;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Notification;

/**
 * Class ExportQueueJob
 *
 * @package App\Jobs
 */
class ExportQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Repositories\ExportQueueRepository
     */
    private $model;

    /**
     * @var int
     */
    public $timeout = 36000;

    /**
     * ExportQueueJob constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->onQueue(config('config.export_tube'));
    }

    /**
     * Handle ExportQueue Job
     *
     * @param \App\Repositories\ExportQueueRepository $exportQueueRepo
     */
    public function handle(ExportQueueRepository $exportQueueRepo)
    {
        $queue = $exportQueueRepo->findByIdExpeditionActor($this->model->id, $this->model->expedition_id, $this->model->actor_id);

        if ($queue === null) {
            $this->delete();

            return;
        }

        try {
            $class = ActorFactory::create($queue->expedition->actors->first()->class);
            $class->processQueue($queue);
            Artisan::call('export:poll');
            $this->delete();
        } catch (Exception $e) {
            $attributes = [
                'error'  => 1
            ];

            $queue->expedition->actors->first()->expeditions()->updateExistingPivot($queue->expedition->id, $attributes);

            $exportQueueRepo->updateMany($attributes, 'expedition_id', $this->model->expedition_id);

            $message = $e->getFile().'<br>'.$e->getLine().'<br>'.$e->getMessage();

            $users = $queue->expedition->project->group->users->push($queue->expedition->project->group->owner);

            Notification::send($users, new NfnExportError($queue->expedition->title, $queue->expedition->id, $message));

            Artisan::call('export:queue');

            $this->delete();
        }
    }
}
