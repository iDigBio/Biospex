<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Biospex\Models\Expedition;

class ExpeditionsTableSeeder extends Seeder
{
    private $expeditions;

    public function run()
    {
        Model::unguard();

        $this->expeditions = $this->loadData();

        foreach ($this->expeditions as $expedition) {
            Expedition::create($expedition);
        }
    }

    public function loadData()
    {
        return [
            [
                'project_id'  => 1,
                'title'       => 'Example Expedition #1',
                'description' => 'A description of Expedition 1 would be included here.',
                'keywords'    => 'Random keywords',
            ],
            [
                'project_id'  => 1,
                'title'       => 'Example Expedition #2',
                'description' => 'A description of Expedition 2 would be included here.',
                'keywords'    => 'Random keywords',
            ],
            [
                'project_id'  => 1,
                'title'       => 'Example Expedition #3',
                'description' => 'A description of Expedition 3 would be included here.',
                'keywords'    => 'Random keywords',
            ],
        ];
    }
}
