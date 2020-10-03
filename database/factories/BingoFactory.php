<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\Bingo::class, function (Faker $faker) {
    return [
        'user_id' => factory(App\Models\User::class),
        'project_id' => factory(App\Models\Project::class),
        'title' => $faker->word,
        'directions' => $faker->word,
        'contact' => $faker->word,
    ];
});
