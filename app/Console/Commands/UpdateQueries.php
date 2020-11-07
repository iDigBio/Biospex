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

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\MongoDbService;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Class UpdateQueries
 *
 * @package App\Console\Commands
 */
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
     * UpdateQueries constructor.
     */
    public function __construct(MongoDbService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Fire command
     */
    public function handle()
    {
        $this->setExported();
        echo 'starting to fix subjects' . PHP_EOL;
        $this->fixSubjects();
    }

    private function setExported()
    {
        $this->service->setCollection('subjects');
        $cursor = $this->service->find();
        $i=0;
        foreach ($cursor as $doc) {
            $this->service->updateOneById(['exported' => false], $doc['_id']);
            $i++;
        }
        echo 'updated docs: ' . $i . PHP_EOL;
    }

    private function fixSubjects()
    {
        $this->service->setCollection('panoptes_transcriptions');
        $cursor = $this->service->find();

        $count = 0;
        foreach ($cursor as $doc) {
            $this->updateSubject($doc['_id'], $doc['subject_subjectId'], $count);
        }
        echo 'updated docs: ' . $count . PHP_EOL;
    }

    private function updateSubject($id, $subjectId, &$count)
    {
        if ($subjectId === null) {
            echo 'SubjectId is null: ' . $id . PHP_EOL;
        }
        $this->service->setCollection('subjects');
        $criteria = ['_id' => $this->service->setMongoObjectId($subjectId)];
        $doc = $this->service->findOne($criteria);

        if ($doc === null) {
            echo 'Cannot find doc ' . $subjectId . PHP_EOL;
        }

        $this->service->updateOneById(['exported' => true], $doc['_id']);
        $count++;
    }
}