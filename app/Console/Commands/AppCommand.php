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

use App\Services\GeoLocateExportService;
use Illuminate\Console\Command;

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
     * @var \App\Services\GeoLocateExportService
     */
    private $service;

    /**
     * AppCommand constructor.
     */
    public function __construct(GeoLocateExportService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $fields = $this->example();

        $this->service->buildGeoLocateExport($fields);
    }

    public function example()
    {
        return json_decode(\Storage::get('test.json'), true);
    }
}