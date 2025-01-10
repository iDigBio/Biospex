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

use App\Services\Api\AwsLambdaApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Class AppCommand
 */
class AppLambdaCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'lambda:test {method}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test sqs lambda code';

    protected string $bucket;

    protected int $expeditionId = 9999; // can be any expedition id

    /**
     * AppCommand constructor.
     */
    public function __construct(protected AwsLambdaApiService $awsLambdaApiService)
    {
        parent::__construct();
        $this->bucket = config('filesystems.disks.s3.bucket');
    }

    /**
     * Handle command.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->argument('method') === 'export') {
            $this->exportTest();
        } elseif ($this->argument('method') === 'explain') {
            $this->explainTest();
        } elseif ($this->argument('method') === 'reconcile') {
            $this->reconcileTest();
        } elseif ($this->argument('method') === 'delete') {
            $this->deleteFiles();
        } elseif ($this->argument('method') === 'tesseract') {
            $this->tesseractTest();
        }
    }

    private function exportTest(): void
    {
        $workingDir = 'scratch/folderName-queueId-actorId-expeditionUuid';
        $attributes = [
            'bucket' => $this->bucket,
            'queueId' => $this->expeditionId,
            'subjectId' => '615da36c65b16554e4781ed9',
            'url' => 'https://cdn.floridamuseum.ufl.edu/Herbarium/45719006-8575-430c-9cd3-18a6ec63f8ed',
            'dir' => $workingDir,
        ];

        Storage::disk('s3')->makeDirectory($workingDir);

        $result = $this->awsLambdaApiService->lambdaInvoke(config('config.aws.lambda_export_function'), $attributes);
        echo $result['Payload']->getContents();

        // $this->awsLambdaApiService->lambdaInvokeAsync(config('config.aws.lambda_export_function'), $attributes);
    }

    private function explainTest(): void
    {
        $attributes = [
            'bucket' => $this->bucket,
            'key' => 'zooniverse/classification/'.$this->expeditionId.'.csv',
            'explanations' => true,
        ];

        // $result = $this->awsLambdaApiService->lambdaInvoke('labelReconciliations', $attributes);
        // echo $result['Payload']->getContents();

        $this->awsLambdaApiService->lambdaInvokeAsync(config('config.aws.lambda_reconciliation_function'), $attributes);
    }

    private function reconcileTest(): void
    {

        $classification = config('zooniverse.directory.classification').'/'.$this->expeditionId.'.csv';
        $lambda_reconciliation = config('zooniverse.directory.lambda-reconciliation').'/'.$this->expeditionId.'.csv';
        Storage::disk('s3')->copy($classification, $lambda_reconciliation);
    }

    private function deleteFiles(): void
    {
        Storage::disk('s3')->delete(config('zooniverse.directory.transcript').'/'.$this->expeditionId.'.csv');
        Storage::disk('s3')->delete(config('zooniverse.directory.summary').'/'.$this->expeditionId.'.csv');
        Storage::disk('s3')->delete(config('zooniverse.directory.reconciled').'/'.$this->expeditionId.'.csv');
        Storage::disk('s3')->delete(config('zooniverse.directory.explained').'/'.$this->expeditionId.'.csv');
    }

    private function tesseractTest(): void
    {
        $attributes = [
            'bucket' => config('filesystems.disks.s3.bucket'),
            'key' => config('zooniverse.directory.lambda-ocr').'/615da36c65b16554e4781ed9.txt',
            'file' => 999,
            'uri' => 'https://cdn.floridamuseum.ufl.edu/herbarium/jpg/092/92321s1.jpg',
        ]; // https://sernecportal.org/imglib/seinet/sernec/FTU/FTU0016/FTU0016693.jpg

        $this->awsLambdaApiService->lambdaInvokeAsync(config('config.aws.lambda_ocr_function'), $attributes);
        // $result = $this->awsLambdaApiService->lambdaInvoke('tesseractOcr', $attributes);
        // echo $result['Payload']->getContents();
    }
}
