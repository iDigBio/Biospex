<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\WeDigBioEventDate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WeDigBioEventTranscription>
 */
class WeDigBioEventTranscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * 'classification_id',
    'project_id',
    'date_id'
     * @return array<string, mixed>
     */
    public function definition()
    {
        $wedigbioDate = WeDigBioEventDate::get()->first();
        $projectIds = Project::inRandomOrder()->limit(5)->get()->pluck('id');

        $fMin = strtotime($wedigbioDate->start_date->toDateString());
        $fMax = strtotime($wedigbioDate->end_date->toDateString());
        $fVal = mt_rand($fMin, $fMax);
        $dateTime = Carbon::parse($fVal, 'UTC');


        return [
            'classification_id' => fake()->randomNumber(6, true),
            'project_id' => $projectIds->random(),
            'date_id' => $wedigbioDate->id,
            'created_at' => $dateTime,
            'updated_at' => $dateTime,
        ];
    }
}
