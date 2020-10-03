<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\AmChart::class, function (Faker $faker) {
    return [
        'project_id' => factory(App\Models\Project::class),
        'series' => $faker->word,
        'data' => $faker->word,
        'queued' => $faker->boolean,
    ];
});
