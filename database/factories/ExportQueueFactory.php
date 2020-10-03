<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\ExportQueue::class, function (Faker $faker) {
    return [
        'expedition_id' => factory(App\Models\Expedition::class),
        'actor_id' => factory(App\Models\Actor::class),
        'stage' => $faker->randomNumber(),
        'queued' => $faker->boolean,
        'count' => $faker->randomNumber(),
        'error' => $faker->boolean,
    ];
});
