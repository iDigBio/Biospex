<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\Notice::class, function (Faker $faker) {
    return [
        'message' => $faker->word,
        'enabled' => $faker->boolean,
    ];
});
