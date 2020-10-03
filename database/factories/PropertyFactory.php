<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\Property::class, function (Faker $faker) {
    return [
        'qualified' => $faker->word,
        'short' => $faker->word,
        'namespace' => $faker->word,
    ];
});
