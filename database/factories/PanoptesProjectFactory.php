<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\PanoptesProject::class, function (Faker $faker) {
    return [
        'project_id' => factory(App\Models\Project::class),
        'expedition_id' => factory(App\Models\Expedition::class),
        'panoptes_project_id' => $faker->randomNumber(),
        'panoptes_workflow_id' => $faker->randomNumber(),
        'subject_sets' => $faker->text,
        'slug' => $faker->slug,
        'title' => $faker->word,
    ];
});
