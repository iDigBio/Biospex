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

use App\Services\Project\HeaderService;
use Illuminate\Console\Command;

/**
 * Class AppCommand
 */
class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * Create a new command instance.
     */
    public function __construct(protected HeaderService $headerService)
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle()
    {
        /*
        $result = $this->headerService->getFirst('project_id', 13);
        if (empty($result)) {
            echo 'Empty'.PHP_EOL;
            $headers['image'] = [
                'assigned',
                'expedition_ids',
                'exported',
                'id',
                'accessURI',
                'ocr',
            ];
        } else {
            echo 'Not Empty'.PHP_EOL;
            $headers = $result->header;
            array_unshift($headers['image'], 'assigned', 'exported', 'expedition_ids', 'id');
            $headers['image'][] = 'ocr';
        }
        dd($headers['image']);
        */

        /*
        echo 'Starting...'.PHP_EOL;
        $this->service->setCollection('subjectsbk');
        $this->service->updateMany([], ['$rename' => ['id' => 'imageId']]);
        echo 'Done'.PHP_EOL;
        */
    }

    /**
     * Decode fields from occurrence then encode to avoid errors.
     *
     * @return false|string
     */
    private function decodeAndEncode($occurrence)
    {
        unset($occurrence['_id'], $occurrence['updated_at'], $occurrence['created_at']);

        foreach ($occurrence as $field => $value) {
            if ($this->isJson($value)) {
                $value = json_decode($value);
            }

            $occurrence[$field] = $value;
        }

        return json_encode($occurrence);
    }

    /**
     * Check if value is json.
     */
    public function isJson($str): bool
    {
        $json = json_decode($str);

        return $json !== false && ! is_null($json) && $str != $json;
    }
}
