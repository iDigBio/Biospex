<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\Workflow::class, function (Faker $faker) {
    return [
        'title' => $faker->word,
        'enabled' => $faker->boolean,
    ];
});
