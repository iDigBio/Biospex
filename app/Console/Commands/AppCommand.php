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

use App\Facades\TranscriptionMapHelper;
use App\Repositories\PanoptesTranscriptionRepository;
use App\Repositories\PusherTranscriptionRepository;
use App\Services\Transcriptions\CreatePanoptesTranscriptionService;
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
     * @var \App\Services\Transcriptions\CreatePanoptesTranscriptionService
     */
    private CreatePanoptesTranscriptionService $createPanoptesTranscriptionService;

    /**
     * @var \App\Repositories\PanoptesTranscriptionRepository
     */
    private PanoptesTranscriptionRepository $panoptesTranscriptionRepository;

    /**
     * @var \App\Repositories\PusherTranscriptionRepository
     */
    private PusherTranscriptionRepository $pusherTranscriptionRepository;

    /**
     * AppCommand constructor.
     */
    public function __construct(
        CreatePanoptesTranscriptionService $createPanoptesTranscriptionService,
        PanoptesTranscriptionRepository $panoptesTranscriptionRepository,
        PusherTranscriptionRepository $pusherTranscriptionRepository
    )
    {
        parent::__construct();
        $this->createPanoptesTranscriptionService = $createPanoptesTranscriptionService;
        $this->panoptesTranscriptionRepository = $panoptesTranscriptionRepository;
        $this->pusherTranscriptionRepository = $pusherTranscriptionRepository;
    }

    /**
     *
     */
    public function handle()
    {
        $result = $this->panoptesTranscriptionRepository->findBy('classification_id', 369292409);

        $newRecord = [];
        foreach ($result->getAttributes() as $field => $value) {
            $newField = TranscriptionMapHelper::encodeTranscriptionFields($field);
            $newRecord[$newField] = $value;
        }

        dd($newRecord);

        $trans = $this->panoptesTranscriptionRepository->findBy('classification_id', 369292409);
        $class = $this->pusherTranscriptionRepository->findBy('classification_id',369292409);

        dd(TranscriptionMapHelper::setScientificName($trans, $class));
        /*
        $transcriptDir = config('config.nfn_downloads_transcript');
        $csvFile = Storage::path($transcriptDir.'/374.csv');
        $this->createPanoptesTranscriptionService->process($csvFile, 374);
        */
    }
}