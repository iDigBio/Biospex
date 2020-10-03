<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\EventTranscription::class, function (Faker $faker) {
    return [
        'classification_id' => $faker->randomNumber(),
        'event_id' => factory(App\Models\Event::class),
        'team_id' => factory(App\Models\EventTeam::class),
        'user_id' => factory(App\Models\EventUser::class),
    ];
});
