<?php

class WorkflowsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('workflows')->truncate();

        Eloquent::unguard();

        $workflows = [
            'OCR' => ['OCR'],
            'Notes From Nature' => ['Notes From Nature'],
            'Notes From Nature II' => ['Notes From Nature II'],
            'OCR -> Notes From Nature' => ['OCR', 'Notes From Nature'],
            'OCR -> Notes From Nature II' => ['OCR', 'Notes From Nature II'],
        ];

        foreach ($workflows as $workflow => $data)
        {
            $result = Workflow::create(['workflow' => $workflow]);
            $i = 0;
            foreach ($data as $value) {
                $actor = Actor::where('title', $value)->first();
                $result->actors()->attach($actor->id, ['order' => $i]);
                $i++;
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}


