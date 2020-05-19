<?php

use App\Models\BingoWord;
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

$factory->define(BingoWord::class, function (Faker $faker) {

    return [
        'bingo_id' => function() {
            return factory(Bingo::class)->create()->id;
        },
        'word' => $this->faker->unique()->words(3, true),
        'definition' => $this->faker->sentences(2, true)
    ];
});
