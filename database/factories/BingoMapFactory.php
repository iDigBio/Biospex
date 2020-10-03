<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\BingoMap::class, function (Faker $faker) {
    return [
        'bingo_id' => factory(App\Models\Bingo::class),
        'uuid' => $faker->uuid,
        'ip' => $faker->word,
        'latitude' => $faker->latitude,
        'longitude' => $faker->longitude,
        'city' => $faker->city,
        'winner' => $faker->boolean,
    ];
});
