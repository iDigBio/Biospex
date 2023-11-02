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

use App\Repositories\ExportQueueRepository;
use App\Services\Actors\Traits\ActorDirectory;
use Aws\Lambda\LambdaClient;
use Illuminate\Console\Command;
use \Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Class AppCommand
 *
 * @package App\Console\Commands
 */
class AppLambdaCommand extends Command
{
    use DispatchesJobs, ActorDirectory;

    /**
     * The console command name.
     */
    protected $signature = 'lambda:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test sqs lambda code';

    /**
     * @var \App\Repositories\ExportQueueRepository
     */
    private ExportQueueRepository $exportQueueRepository;

    /**
     * AppCommand constructor.
     */
    public function __construct(
        ExportQueueRepository $exportQueueRepository
    ) {
        parent::__construct();
        $this->exportQueueRepository = $exportQueueRepository;
    }

    /**
     * Handle command.
     *
     * @return void
     */
    public function handle()
    {
        $this->imageTar();
        //$this->imageProcess();
    }

    /**
     * @return void
     */
    public function imageTar()
    {
        $exportQueue = $this->exportQueueRepository->findWith(1, ['expedition']);
        $this->setFolder($exportQueue->id, $exportQueue->actor_id, $exportQueue->expedition->uuid);
        $this->setDirectories();


        $client = $this->getLambdaClient();

        $data = [
            'queueId' => $exportQueue->id, //event.queueId;
            'sourcePath' => $this->workingDir, //event.sourcePath;
            'outputFilename' =>  md5($this->folderName) //event.outputFilename;
        ];

        $start_time = microtime(true);

        $result = $client->invoke([
            // The name your created Lamda function
            'FunctionName'   => 'imageTarGz',
            'Payload'        => json_encode($data),
        ]);

        echo $result['Payload'] . PHP_EOL;

        // End clock time in seconds
        $end_time = microtime(true);

        // Calculate script execution time
        $execution_time = ($end_time - $start_time);
        echo " Execution time of script = ".$execution_time." sec" . PHP_EOL;
    }

    /**
     * @return void
     */
    public function imageProcess()
    {
        $client = $this->getLambdaClient();

        $data = $this->generateUrls(1);

        collect($data)->each(function($image) use($client) {
            $result = $client->invoke([
                // The name your created Lamda function
                'FunctionName'   => 'imageProcessExport',
                'Payload'        => json_encode($image),
                'InvocationType' => 'Event',
            ]);

            echo $result['Payload'] . PHP_EOL;
        });
    }

    /**
     * Temp method to generate urls for testing.
     *
     * @param int $total
     * @return array
     */
    public function generateUrls(int $total): array
    {
        $files = $this->exportQueueFileRepository->findBy('queue_id', 10)->limit($total)->get();

        return $files->map(function ($file) {
            return [
                'queueId' => $file->queue_id,
                'subjectId'  => $file->subject_id,
                'url' => $file->url,
                'dir' => "scratch/testing-scratch",
            ];
        })->toArray();
    }

    /**
     * @return \Aws\Lambda\LambdaClient
     */
    private function getLambdaClient(): LambdaClient
    {
        return new LambdaClient([
            'credentials' => [
                'key'    => config('config.aws_access_key'),
                'secret' => config('config.aws_secret_key'),
            ],
            'version'     => '2015-03-31',
            'region'      => config('config.aws_default_region'),
        ]);
    }
}