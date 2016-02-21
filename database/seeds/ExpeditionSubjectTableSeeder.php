<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class ExpeditionSubjectTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $expeditions = App\Models\Expedition::all();

        foreach ($expeditions as $expedition) {
            $subjects = App\Models\Subject::where('expedition_ids', 'size', 0)->get();
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
