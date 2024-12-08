<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Services\Csv\Csv;
use Illuminate\Console\Command;

class PopulateCitiesTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:populate-cities-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(protected Csv $csv)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws \League\Csv\Exception
     */
    public function handle()
    {
        $this->csv->readerCreateFromPath(storage_path('worldcities.csv'));
        $this->csv->setHeaderOffset();
        $rows = $this->csv->getRecords();
        foreach ($rows as $row) {
            City::create([
                'city' => $row['city'],
                'latitude' => $row['lat'],
                'longitude' => $row['lng'],
            ]);
        }
        echo "Cities table populated successfully.\n";
    }
}
