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

use App\Repositories\Interfaces\PanoptesTranscription;
use App\Services\MongoDbService;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class UpdateQueries extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'update:queries';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';

    /**
     * @var \App\Services\MongoDbService
     */
    private $service;

    /**
     * @var \App\Repositories\Interfaces\PanoptesTranscription
     */
    private $transcription;

    /**
     * UpdateQueries constructor.
     *
     * @param \App\Services\MongoDbService $service
     * @param \App\Repositories\Interfaces\PanoptesTranscription $transcription
     */
    public function __construct(MongoDbService $service, PanoptesTranscription $transcription)
    {
        parent::__construct();
        $this->service = $service;
        $this->transcription = $transcription;
    }

    /**
     * Fire command
     */
    public function handle()
    {
        //$this->fixSubjectId();
        $this->fixClassificationIdForPusher();

    }

    /**
     * Fixes where subject Id was changed.
     */
    public function fixSubjectId()
    {
        $this->service->setCollection('panoptes_transcriptions');
        $attributes = ['$rename' => ['subject_Subject_ID' => 'subject_subjectId']];
        $criteria = ['subject_Subject_ID' => ['$exists' => true]];
        $this->service->updateMany($attributes, $criteria);
    }

    public function fixClassificationIdForPusher()
    {
        $this->service->setCollection('pusher_transcriptions');
        $criteria = ['classification_id' => ['$type' => 'string']];
        $cursor = $this->service->find($criteria);
        foreach ($cursor as $doc) {
            dd($doc);
        }

    }

    public function fixMissingExpeditionId()
    {
        
    }

}