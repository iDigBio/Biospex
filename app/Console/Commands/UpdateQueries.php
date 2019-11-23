<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\PanoptesTranscription;
use App\Services\MongoDbService;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class UpdateQueries extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'update:queries';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';

    /**
     * @var \App\Services\MongoDbService
     */
    private $service;

    /**
     * @var \App\Repositories\Interfaces\PanoptesTranscription
     */
    private $transcription;

    /**
     * UpdateQueries constructor.
     *
     * @param \App\Services\MongoDbService $service
     * @param \App\Repositories\Interfaces\PanoptesTranscription $transcription
     */
    public function __construct(MongoDbService $service, PanoptesTranscription $transcription)
    {
        parent::__construct();
        $this->service = $service;
        $this->transcription = $transcription;
    }

    /**
     * Fire command
     */
    public function handle()
    {
        $this->service->setCollection('panoptes_transcriptions');

        $query = ['classification_finished_at.timezone' => 'UTC'];

        $cursor = $this->service->find($query);

        $cursor->setTypeMap([
            'array'    => 'array',
            'document' => 'array',
            'root'     => 'array'
        ]);

        $i = 0;
        foreach ($cursor as $doc) {
            $attributes = [
                'classification_started_at' => $doc['classification_started_at']['date'],
                'classification_finished_at' => $doc['classification_finished_at']['date'],
            ];

            $this->transcription->update($attributes, $doc['_id']);
            $i++;
            echo $i . PHP_EOL;
        }

    }

}