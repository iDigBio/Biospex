<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\Actor::class, function (Faker $faker) {
    return [
        'title' => $faker->word,
        'url' => $faker->url,
        'class' => $faker->word,
        'private' => $faker->boolean,
    ];
});
