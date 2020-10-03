<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\EventTeam::class, function (Faker $faker) {
    return [
        'uuid' => $faker->uuid,
        'event_id' => factory(App\Models\Event::class),
        'title' => $faker->word,
    ];
});
