<?php
/*
 * Copyright (c) 2022. Biospex
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

namespace App\Jobs;

use App\Models\User;
use App\Notifications\JobError;
use App\Repositories\PusherTranscriptionRepository;
use App\Services\Transcriptions\CreatePusherTranscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Class PusherTranscriptionJob
 *
 * @package App\Jobs
 */
class PusherTranscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * @var \App\Repositories\PusherTranscriptionRepository
     */
    private PusherTranscriptionRepository $pusherTranscriptionRepository;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->onQueue(config('config.queue.pusher_process'));
    }

    /**
     * Executes moving pusher classifications from mysql to pusher transcriptions in mongodb.
     * Cron runs every 5 minutes.
     *
     * @return void
     */
    public function handle(CreatePusherTranscriptionService $createPusherTranscriptionService) {
        try {

            $createPusherTranscriptionService->process();
            $this->delete();

            return;
        } catch (\Exception $e) {
            $user = User::find(1);
            $messages = [
                'Message:'.$e->getFile().': '.$e->getLine().' - '.$e->getMessage(),
            ];
            $user->notify(new JobError(__FILE__, $messages));

            $this->delete();

            return;
        }
    }
}
