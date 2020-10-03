<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\Profile::class, function (Faker $faker) {
    return [
        'user_id' => factory(App\Models\User::class),
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'timezone' => $faker->word,
        'avatar_file_name' => $faker->word,
        'avatar_file_size' => $faker->randomNumber(),
        'avatar_content_type' => $faker->word,
        'avatar_updated_at' => $faker->dateTime(),
    ];
});
