<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\Group::class, function (Faker $faker) {
    return [
        'uuid' => $faker->uuid,
        'user_id' => factory(App\Models\User::class),
        'title' => $faker->word,
    ];
});
