<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use ApiClients\Client\Pusher\Event;
use React\EventLoop\Factory;
use ApiClients\Client\Pusher\AsyncClient;

class NfnClassificationPusher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfn:pusher';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $loop = Factory::create();
        $client = AsyncClient::create($loop, '79e8e05ea522377ba6db');

        $client->channel('panoptes')->subscribe(
            function (Event $event) { // Gets called for each incoming event
                //echo 'Data: ', json_encode($event->getData()), PHP_EOL;

                $data = $event->getData();
                if ($event->getEvent() === 'classification' && $data['project_id'] === 1558)
                {
                    echo 'Data: ', json_encode($event->getData()), PHP_EOL;
                }
            },
            function ($e) { // Gets called on errors
                echo (string)$e;
            },
            function () { // Gets called when the end of the stream is reached
                echo 'Done!', PHP_EOL;
            }
        );

        $loop->run();

    }
}
