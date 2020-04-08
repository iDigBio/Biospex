<?php

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

$factory->define(Bingo::class, function (Faker $faker) {
    return [
        'project_id' => 13,
        'user_id' => 1,
        'title' => $this->faker->words(3, true),
        'directions' => $faker->sentence(6)
    ];
});