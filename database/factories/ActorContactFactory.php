<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\ActorContact::class, function (Faker $faker) {
    return [
        'actor_id' => factory(App\Models\Actor::class),
        'email' => $faker->safeEmail,
    ];
});
