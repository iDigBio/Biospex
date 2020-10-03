<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\Team::class, function (Faker $faker) {
    return [
        'team_category_id' => factory(App\Models\TeamCategory::class),
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->safeEmail,
        'title' => $faker->word,
        'department' => $faker->word,
        'institution' => $faker->word,
    ];
});
