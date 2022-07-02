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

use App\Jobs\OcrCreateJob;
use App\Models\Header;
use App\Models\Property;
use App\Notifications\DarwinCoreImportError;
use App\Notifications\ImportComplete;
use App\Repositories\HeaderRepository;
use App\Repositories\ProjectRepository;
use App\Services\Csv\Csv;
use App\Services\File\FileService;
use App\Services\FixFields\FixFieldsBase;
use App\Services\MongoDbService;
use App\Services\Process\DarwinCore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Class AppCommand
 *
 * @package App\Console\Commands
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
     * @var \App\Repositories\ProjectRepository
     */
    private ProjectRepository $projectRepo;

    /**
     * @var \App\Services\Process\DarwinCore
     */
    private DarwinCore $dwcProcess;

    /**
     * @var \App\Services\File\FileService
     */
    private FileService $fileService;

    /**
     * @var \App\Services\Csv\Csv
     */
    private Csv $csv;

    /**
     * @var \App\Services\MongoDbService
     */
    private MongoDbService $service;

    /**
     * @var \App\Repositories\HeaderRepository
     */
    private HeaderRepository $headerRepository;

    /**
     * @var \App\Services\FixFields\FixFieldsBase
     */
    private FixFieldsBase $base;

    /**
     * AppCommand constructor.
     *
     * @param \App\Repositories\ProjectRepository $projectRepo
     * @param \App\Services\Process\DarwinCore $dwcProcess
     * @param \App\Services\File\FileService $fileService
     * @param \App\Services\Csv\Csv $csv
     * @param \App\Services\MongoDbService $service
     * @param \App\Repositories\HeaderRepository $headerRepository
     */
    public function __construct(
        ProjectRepository $projectRepo,
        DarwinCore $dwcProcess,
        FileService $fileService,
        Csv $csv,
        MongoDbService $service,
        HeaderRepository $headerRepository,
        FixFieldsBase $base
    ) {
        parent::__construct();
        $this->projectRepo = $projectRepo;
        $this->dwcProcess = $dwcProcess;
        $this->fileService = $fileService;
        $this->csv = $csv;
        $this->service = $service;
        $this->headerRepository = $headerRepository;
        $this->base = $base;
    }

    /**
     *
     */
    public function handle()
    {
        //$scratchFileDir = Storage::path(config('config.scratch_dir').'/test');

        //$project = $this->projectRepo->getProjectForDarwinImportJob(13);

        /*
        $this->fileService->makeDirectory($scratchFileDir);
        $importFilePath = Storage::path(config('config.scratch_dir') . '/test.zip');
        $this->fileService->unzip($importFilePath, $scratchFileDir);
        */

        //$this->dwcProcess->process($project->id, $scratchFileDir);

        /*
        $header =Header::where('project_id', 13)->get()->first()->header;
        asort($header);
        dd($header);
        */

        /*
        $properties = Property::orderBy('short')->get();
        $properties->each(function ($property){
            Storage::append('test.php', $property->short);
        });
        echo "done" . PHP_EOL;
        */

        /*
        $this->service->setCollection('subjects');
        $count = $this->service->count([
            'Identifier'['$exists'true],
        ]);
        dd($count);
        */

        // 6298bb7bc5143f1cc750a3f1

        /*
        $this->service->setCollection('subjects');
        $criteria = ['project_id'13];
        $attributes = ['$rename'['format76d1''format']];
        $result = $this->service->updateMany($attributes, $criteria);
        */

        /*
        $header = $this->getHeader();

        if (($badIndex = array_search('format76d1', $header)) !== false) {
            //unset($header[$badIndex]);
            echo $badIndex . PHP_EOL;
            echo "true" . PHP_EOL;
            exit;
        }
        echo "false" . PHP_EOL;
        exit;
        */


        $var1 = "scientificName";
        $var2 = "scientificname";
        if (strcasecmp($var1, $var2) == 0) {
            echo '$var1 is equal to $var2 in a case-insensitive string comparison';
        }
        exit;

        $record = $this->headerRepository->findBy('project_id', 15);
        dd($record->header['occurrence']);

    }
}