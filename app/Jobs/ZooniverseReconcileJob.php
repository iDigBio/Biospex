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

use App\Jobs\Traits\SkipZooniverse;
use App\Models\User;
use App\Notifications\JobError;
use App\Services\Reconcile\ReconcileProcess;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Class ZooniverseReconcileJob
 *
 * @package App\Jobs
 */
class ZooniverseReconcileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SkipZooniverse;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 300;

    /**
     * @var int
     */
    private int $expeditionId;

    /**
     * Create a new job instance.
     *
     * @param int $expeditionId
     */
    public function __construct(int $expeditionId)
    {
        $this->onQueue(config('config.queue.reconcile'));
        $this->expeditionId = $expeditionId;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Reconcile\ReconcileProcess $service
     */
    public function handle(ReconcileProcess $service)
    {
        if ($this->skipReconcile($this->expeditionId)) {
            $this->delete();
            return;
        }

        $service->process($this->expeditionId);
    }

    /**
     * Prevent job overlap using expeditionId.
     *
     * @return \Illuminate\Queue\Middleware\WithoutOverlapping[]
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping($this->expeditionId)];
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $user = User::find(1);
        $messages = [
            t('Error: %s', $exception->getMessage()),
            t('File: %s', $exception->getFile()),
            t('Line: %s', $exception->getLine()),
        ];
        $user->notify(new JobError(__FILE__, $messages));
    }
}
