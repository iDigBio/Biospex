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

use App\Services\Csv\Csv;
use App\Services\MongoDbService;
use Illuminate\Console\Command;

/**
 * Class ReconcileHeaderCommand
 */
class ReconcileHeaderCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'reconcile:header';

    /**
     * The console command description.
     */
    protected $description = 'Used to create reconcile header for mapping.';

    /**
     * AppCommand constructor.
     */
    public function __construct(protected MongoDbService $mongoDbService, protected Csv $csv)
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $this->mongoDbService->setCollection('panoptes_transcriptions');
        $options = ['typeMap' => ['root' => 'array', 'document' => 'array']];
        $results = $this->mongoDbService->find([], $options);

        $header = collect();
        foreach ($results as $doc) {
            $flipped = collect($doc)->keys()->flip();

            $header = $header->merge($flipped);

            $header->pull('_id');
        }

        $keys = $header->keys()->toArray();
        asort($keys, SORT_STRING | SORT_FLAG_CASE);

        $this->csv->writerCreateFromPath(storage_path('/app/headers.csv'));

        foreach ($keys as $row) {
            $this->csv->insertOne(['column' => $row]);
        }
    }
}
