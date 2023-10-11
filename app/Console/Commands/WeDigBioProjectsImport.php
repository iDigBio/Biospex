<?php

namespace App\Console\Commands;

use App\Models\PanoptesProject;
use App\Models\WeDigBioProject;
use App\Services\Csv\Csv;
use Illuminate\Console\Command;

class WeDigBioProjectsImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wedigbio:ingest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var \App\Services\Csv\Csv
     */
    private Csv $csv;

    public function __construct(Csv $csv) {
        parent::__construct();
        $this->csv = $csv;
    }

    /**
     * Execute the console command.
     * Project_id, Workflow_id, Workflow_name
     *
     * @return int
     */
    public function handle()
    {
        $file = \Storage::path('wedigbio_expeditions_Oct_10.csv');
        try {
            $this->csv->readerCreateFromPath($file);
            $this->csv->setDelimiter();
            $this->csv->setEnclosure();
            $this->csv->setHeaderOffset();

            $header = $this->csv->getHeader();

            $data = [];
            $rows = $this->csv->getRecords($header);
            foreach ($rows as $row) {
                $this->processRow($row, $data);
            }

            //$this->saveData($data);
            echo 'done' . PHP_EOL;
        }
        catch (\Throwable $throwable) {
            echo $throwable->getMessage() . PHP_EOL;
        }
    }

    public function processRow($row, &$data)
    {
        $panoptesProjectCount = PanoptesProject::where('panoptes_workflow_id', $row['Workflow_id'])->count();
        if ($panoptesProjectCount > 0) return;

        $weDigBioProjectCount = WeDigBioProject::where('panoptes_workflow_id', $row['Workflow_id'])->count();
        if ($weDigBioProjectCount > 0) return;

        WeDigBioProject::create([
            'panoptes_project_id' => $row['Project_id'],
            'panoptes_workflow_id' => $row['Workflow_id'],
            'title' => 'Notes From Nature'
        ]);
    }

    public function saveData(array $data = [])
    {
        $filePath = \Storage::path('wedigbio_extra.csv');
        $this->csv->writerCreateFromPath($filePath);
        $this->csv->insertOne(array_keys(reset($data)));
        $this->csv->insertAll($data);
    }
}
