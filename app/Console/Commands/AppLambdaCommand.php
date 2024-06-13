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
 *
 * @package App\Console\Commands
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

    /**
     * @var \App\Services\Api\AwsLambdaApiService
     */
    private AwsLambdaApiService $awsLambdaApiService;

    /**
     * AppCommand constructor.
     */
    public function __construct(AwsLambdaApiService $awsLambdaApiService)
    {
        parent::__construct();
        $this->awsLambdaApiService = $awsLambdaApiService;
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
        }
    }

    private function exportTest(): void
    {
        $workingDir = "scratch/folderName-queueId-actorId-expeditionUuid";
        $attributes = [
            'bucket' => config('filesystems.disks.s3.bucket'),
            'queueId'    => 999999,
            'subjectId'  => "615da36c65b16554e4781ed9",
            'url'        => "https://cdn.floridamuseum.ufl.edu/herbarium/jpg/092/92321s1.jpg",
            'dir'        => $workingDir,
        ];

        Storage::disk('s3')->makeDirectory($workingDir);

        //$result = $this->awsLambdaApiService->lambdaInvoke(config('config.aws.lambda_export_function'), $attributes);
        //echo $result['Payload']->getContents();

        $this->awsLambdaApiService->lambdaInvokeAsync(config('config.aws.lambda_export_function'), $attributes);
    }

    private function explainTest(): void
    {
        $attributes = [
            'bucket'       => 'biospex-dev',
            'key'          => 'zooniverse/classification/999999.csv',
            'explanations' => true,
        ];

        $result = $this->awsLambdaApiService->lambdaInvoke('labelReconciliations', $attributes);
        echo $result['Payload']->getContents();

        //$this->awsLambdaApiService->lambdaInvokeAsync('labelReconciliations', $attributes);
    }

    private function reconcileTest(): void
    {
        $classification = config('zooniverse.directory.classification') . '/999999.csv';
        $lambda_reconciliation = config('zooniverse.directory.lambda-reconciliation') . '/999999.csv';
        \Storage::disk('s3')->copy($classification, $lambda_reconciliation);
    }
}