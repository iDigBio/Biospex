<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\Download::class, function (Faker $faker) {
    return [
        'uuid' => $faker->uuid,
        'expedition_id' => factory(App\Models\Expedition::class),
        'actor_id' => factory(App\Models\Actor::class),
        'file' => $faker->word,
        'type' => $faker->word,
    ];
});
