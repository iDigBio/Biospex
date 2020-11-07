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

use App\Models\User;
use App\Notifications\JobError;
use App\Services\Process\ExpertReconcilePublishProcess;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use League\Csv\CannotInsertRecord;

/**
 * Class NfnExpertReviewPublishJob
 *
 * @package App\Jobs
 */
class NfnExpertReviewPublishJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var null
     */
    private $expeditionId;

    /**
     * NfnExpertReviewPublishJob constructor.
     *
     * @param string $expeditionId
     */
    public function __construct(string $expeditionId)
    {
        $this->expeditionId = $expeditionId;
        $this->onQueue(config('config.classification_tube'));
    }

    /**
     * Handle Job.
     *
     * @param \App\Services\Process\ExpertReconcilePublishProcess $expertReconcilePublishProcess
     */
    public function handle(ExpertReconcilePublishProcess $expertReconcilePublishProcess)
    {

        try {
            $expertReconcilePublishProcess->publishReconciled($this->expeditionId);
        } catch (CannotInsertRecord | Exception $e) {
            $user = User::find(1);
            $messages = [
                'Expedition Id: '.$this->expeditionId,
                'Message:' . $e->getFile() . ': ' . $e->getLine() . ' - ' . $e->getMessage()
            ];
            $user->notify(new JobError(__FILE__, $messages));
        }

        $this->delete();
    }
}
