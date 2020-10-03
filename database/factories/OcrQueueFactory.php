<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\OcrQueue::class, function (Faker $faker) {
    return [
        'project_id' => factory(App\Models\Project::class),
        'expedition_id' => factory(App\Models\Expedition::class),
        'total' => $faker->randomNumber(),
        'processed' => $faker->randomNumber(),
        'status' => $faker->randomNumber(),
        'error' => $faker->boolean,
    ];
});
