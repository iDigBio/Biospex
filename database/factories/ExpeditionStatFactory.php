<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\ExpeditionStat::class, function (Faker $faker) {
    return [
        'expedition_id' => factory(App\Models\Expedition::class),
        'local_subject_count' => $faker->randomNumber(),
        'subject_count' => $faker->randomNumber(),
        'transcriptions_total' => $faker->randomNumber(),
        'transcriptions_completed' => $faker->randomNumber(),
        'percent_completed' => $faker->randomFloat(),
    ];
});
