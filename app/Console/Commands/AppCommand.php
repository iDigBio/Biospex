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

use App\Models\Subject;
use App\Models\User;
use App\Notifications\JobComplete;
use App\Services\Csv\Csv;
use App\Services\MongoDbService;
use App\Services\Reconcile\ReconcileProcess;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use MongoDB\BSON\ObjectId;
use Pheanstalk\Pheanstalk;

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
     * AppCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(Csv $csv)
    {
        $csv->readerCreateFromPath(storage_path('app/428-fix.csv'));
        $csv->setHeaderOffset();
        $records = $csv->getRecords();

        foreach ($records as $row) {
            $subject = Subject::find($row['subjectId']);
            $subject['expedition_ids'] = array_merge($subject['expedition_ids'], [428]);
            $subject->save();
        }
    }

    public function clean()
    {
        /*
        File::cleanDirectory(Storage::disk('efs')->path(config('config.zooniverse_dir.classification')));
        File::cleanDirectory(Storage::disk('efs')->path(config('config.zooniverse_dir.reconcile')));
        File::cleanDirectory(Storage::disk('efs')->path(config('config.zooniverse_dir.reconciled')));
        File::cleanDirectory(Storage::disk('efs')->path(config('config.zooniverse_dir.transcript')));
        File::cleanDirectory(Storage::disk('efs')->path(config('config.zooniverse_dir.summary')));
        File::cleanDirectory(Storage::disk('efs')->path(config('config.zooniverse_dir.explained')));
        */
        // "aws s3 mv s3://biospex-app/scratch/2-2-c5afceb7-b475-4628-8cdc-6fb2d0b939d5 /efs/batch/ --recursive"
    }

    /**
     * peek, peakReady, peekBuried, peakDelayed
     * https://panoptesuploads.blob.core.windows.net/private/workflow_classifications_export/74d14c7b-10dd-4aae-affe-a229d7daf8fa.csv?sp=r&sv=2017-11-09&se=2022-09-06T20%3A43%3A18Z&sr=b&sig=gCyUMcgFllP9wRvrPzcmx19NkTZy%2B5ih76iOk%2BJa2fk%3D
     * https://panoptesuploads.blob.core.windows.net/private/workflow_classifications_export/828fc103-fcf6-4af7-9d33-faf9cc4691f3.csv?sp=r&sv=2017-11-09&se=2022-09-06T20%3A43%3A18Z&sr=b&sig=cuV%2FVtIjJXmEXCt0WASP7JFX%2BKrs2HssqNjNebs5qFI%3D
     */
    public function clearTube($tube)
    {
        /*
        try
        {
            $pheanstalk = Queue::getPheanstalk();
            $pheanstalk->useTube('default');

            while($job = $pheanstalk->peekReady())
            {
                $pheanstalk->delete($job);
            }
        }
        catch(\Exception $e){}
        */
    }
}