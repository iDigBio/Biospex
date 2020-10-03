<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\BingoWord::class, function (Faker $faker) {
    return [
        'bingo_id' => factory(App\Models\Bingo::class),
        'word' => $faker->word,
        'definition' => $faker->word,
    ];
});
