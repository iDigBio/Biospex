<?php

class ExpeditionSubjectTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $expeditions = Expedition::all();

        foreach ($expeditions as $expedition) {
            $subjects = Subject::where('expedition_ids', 'size', 0)->get();
            $i = 0;
            foreach ($subjects as $subject) {
                if ($i == 300) {
                    break;
                }
                // add expedition ids to subjects
                $expedition->subjects()->attach($subject);
                $i++;
            }
        }
    }
}
