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

$factory->define(\App\Models\EventTranscription::class, function (Faker $faker) {

    return [
        'classification_id' => $faker->unique()->randomNumber(8),
        'event_id' => function() {
            return factory(App\Models\Event::class)->create()->id;
        },
        'team_id' => function() {
            return factory(App\Models\EventTeam::class)->create()->id;
        },
        'user_id' => function() {
            return factory(App\Models\Event::class)->create()->id;
        }
    ];
});
