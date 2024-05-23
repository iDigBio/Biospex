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
    protected $signature = 'lambda:test';

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
        // https://js2qavzr5g4kmzi5ssmg7gkw240wfvln.lambda-url.us-east-2.on.aws/

        $attributes = [
            'bucket' => 'biospex-dev',
            'key' => 'zooniverse/classification/999999.csv',
            'explanations' => true
        ];

        $result = $this->awsLambdaApiService->lambdaInvoke('labelReconciliations', $attributes);

        echo $result['Payload']->getContents();
    }
}

/*
 Folders for old => new reconciliation service
input_file: classification/ => classification
--reconciled: reconcile/ => reconciled/
--unreconciled: transcript/ => transcript/
--summary: summary/ => summary/
--reconciled --explanations: explained/ => explained/

 */