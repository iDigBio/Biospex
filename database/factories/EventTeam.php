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

$factory->define(\App\Models\EventTeam::class, function (Faker $faker) {

    return [
        'event_id' => function() {
            return factory(App\Models\Event::class)->create()->id;
        },
        'uuid' => $faker->uuid(),
        'title' => $this->faker->words(3, true),
    ];
});
