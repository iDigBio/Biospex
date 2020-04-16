<?php

use App\Models\BingoMap;
use App\Models\Bingo;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(BingoMap::class, function (Faker $faker) {

    return [
        'bingo_id' => function() {
            return factory(Bingo::class)->create()->id;
        },
        'ip' => $this->faker->ipv6(), // ipv4()
        'latitude' => $this->faker->latitude(),
        'longitude' => $this->faker->longitude(),
        'city' => $this->faker->city()
    ];
});
