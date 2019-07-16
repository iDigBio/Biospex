<?php

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

$factory->define(\App\Models\Event::class, function (Faker $faker) {

    $start = $faker->dateTimeBetween('-5 days', 'now');

    return [
        'project_id' => 13,
        'owner_id' => 1,
        'title' => $this->faker->words(3, true),
        'description' => $faker->sentence(6),
        'contact' => $faker->name,
        'contact_email' => $faker->unique()->safeEmail,
        'start_date' => $start,
        'end_date' => $faker->dateTimeBetween($start, '+3 weeks'),
        'timezone' => 'America/New_York'
    ];
});