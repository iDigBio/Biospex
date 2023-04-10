<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\WeDigBioEventDate;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        $wedigbioDateId = WeDigBioEventDate::get()->first()->id;
        $projectIds = Project::inRandomOrder()->limit(5)->get()->pluck('id');

        return [
            'classification_id' => fake()->randomNumber(6, true),
            'project_id' => $projectIds->random(),
            'date_id' => $wedigbioDateId
        ];
    }
}
