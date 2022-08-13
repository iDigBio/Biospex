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

use App\Repositories\ExportQueueFileRepository;
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
    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'lambda:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test sqs lambda code';

    /**
     * @var \App\Repositories\ExportQueueFileRepository
     */
    private ExportQueueFileRepository $exportQueueFileRepository;

    /**
     * AppCommand constructor.
     */
    public function __construct(
        ExportQueueFileRepository $exportQueueFileRepository
    ) {
        parent::__construct();
        $this->exportQueueFileRepository = $exportQueueFileRepository;
    }

    /**
     * Handle command.
     *
     * @return void
     */
    public function handle()
    {
        $this->lambdaClient();
    }

    /**
     * @return void
     */
    public function lambdaClient()
    {
        $client = new LambdaClient([
            'credentials' => [
                'key'    => config('config.aws_access_key'),
                'secret' => config('config.aws_secret_key'),
            ],
            'version'     => '2015-03-31',
            'region'      => config('config.aws_default_region'),
        ]);

        $data = $this->generateUrls(1);

        collect($data)->each(function($image) use($client) {
            $result = $client->invoke([
                // The name your created Lamda function
                'FunctionName'   => 'imageExportProcess',
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
        $files = $this->exportQueueFileRepository->findBy('queue_id', 1)->limit($total)->get();

        return $files->map(function ($file) {
            return [
                'queueId' => $file->queue_id,
                'subjectId'  => $file->subject_id,
                'url' => $file->url,
                'dir' => "scratch/testing-scratch",
            ];
        })->toArray();
    }
}