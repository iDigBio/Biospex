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

namespace App\Jobs;

use App\Models\ExportQueue as Model;
use App\Repositories\Interfaces\ExportQueue;
use App\Services\Actor\ActorFactory;
use Artisan;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\NfnExportError;
use Notification;

class ExportQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 36000;

    /**
     * @var ExportQueue
     */
    private $model;

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
     * @param ExportQueue $exportQueueContract
     */
    public function handle(ExportQueue $exportQueueContract)
    {
        $queue = $exportQueueContract->findByIdExpeditionActor($this->model->id, $this->model->expedition_id, $this->model->actor_id);

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
            event('actor.pivot.error', $queue->expedition->actors->first());

            $attributes = ['queued' => 0, 'error' => 1];
            $exportQueueContract->updateMany($attributes, 'expedition_id', $this->model->expedition_id);

            $message = trans('pages.nfn_export_error', [
                'title'   => $queue->expedition->title,
                'id'      => $queue->expedition->id,
                'message' => $e->getFile().':'.$e->getLine().' - '.$e->getMessage(),
            ]);

            $users = $queue->expedition->project->group->users->push($queue->expedition->project->group->owner);

            Notification::send($users, new NfnExportError($message));

            Artisan::call('export:queue');

            $this->delete();
        }
    }
}
