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

namespace App\Console\Commands;

use App\Facades\DateHelper;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Services\Model\EventTranscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Repositories\Interfaces\PanoptesTranscription
     */
    private $transcription;

    /**
     * @var \App\Services\Model\EventTranscriptionService
     */
    private $service;

    /**
     * @var \App\Repositories\Interfaces\Expedition
     */
    private $expeditionContract;

    /**
     * AppCommand constructor.
     */
    public function __construct(
        PanoptesTranscription $transcription,
        EventTranscriptionService $service, Expedition $expeditionContract) {
        parent::__construct();
        $this->transcription = $transcription;
        $this->service = $service;
        $this->expeditionContract = $expeditionContract;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        // 127514383
        $transcript = $this->transcription->findBy('classification_id', 280166500);
        $expedition = $this->expeditionContract->find($transcript->subject_expeditionId);

        $count = $this->service->classificationIdExists($transcript->classification_id);
        if ($count) {
            return;
        }

        $user = 'Austinmast';
        $projectId = 13;
        // $transcript->classification_finshed_at
        $finishedDate = Carbon::parse(, 'UTC')->format('Y-m-d H:i:s');

        $result = $this->service->checkEventsExist($user, $projectId, $finishedDate);

        dd($count);



    }

}