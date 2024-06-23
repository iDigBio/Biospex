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

use App\Events\ImageExportEvent;
use App\Events\LabelReconciliationEvent;
use App\Jobs\TesseractOcrCreateJob;
use App\Jobs\TesseractOcrProcessJob;
use App\Models\Subject;
use App\Repositories\ExportQueueFileRepository;
use App\Repositories\OcrQueueRepository;
use App\Repositories\SubjectRepository;
use App\Services\Ocr\TesseractOcrService;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

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
     * @var \App\Services\Ocr\TesseractOcrService
     */
    private TesseractOcrService $tesseractOcrService;

    /**
     * @var \App\Repositories\ExportQueueFileRepository
     */
    private ExportQueueFileRepository $exportQueueFileRepository;

    /**
     * @var \App\Repositories\OcrQueueRepository
     */
    private OcrQueueRepository $ocrQueueRepository;

    /**
     * Create a new command instance.
     */
    public function __construct(
        TesseractOcrService $tesseractOcrService,
        ExportQueueFileRepository $exportQueueFileRepository,
        OcrQueueRepository $ocrQueueRepository
    ) {
        parent::__construct();
        $this->tesseractOcrService = $tesseractOcrService;
        $this->exportQueueFileRepository = $exportQueueFileRepository;
        $this->ocrQueueRepository = $ocrQueueRepository;
    }

    /**
     * @return void
     */
    public function handle()
    {
        /*$files = $this->tesseractOcrService->getUnprocessedOcrQueueFiles(3);
        $files->each(function($file){
            if ($file->tries < 3) {
                $this->info('Sending to invoke ' . $file->id);
                return;
            }

            $this->info('Max tries reached ' . $file->id);
            //$file->processed = 1;
            //$file->message = t('Error: Excceded maximum tries.');
            //$file->save();

        });*/


        $records = Subject::where('project_id', 13)->where('expedition_ids', 462)->get();
        $records->each(function ($record) {
            $record->ocr = '';
            $record->save();
        });
        exit;


        //$total = $this->tesseractOcrService->getSubjectCountForOcr(13, 462);
        //dd($total);

        /*$records = Subject::where('project_id', 13)->where('expedition_ids', 462)->get();
        $records->each(function ($record) {
            $record->ocr = '';
            $record->save();
        });*/

        //echo $this->tesseractOcrService->getSubjectCountForOcr(13, 462).PHP_EOL;
        //TesseractOcrCreateJob::dispatch(13, 462);
    }
}