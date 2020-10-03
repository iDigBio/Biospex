<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\Event::class, function (Faker $faker) {
    return [
        'project_id' => factory(App\Models\Project::class),
        'owner_id' => factory(App\Models\User::class),
        'title' => $faker->word,
        'description' => $faker->text,
        'hashtag' => $faker->word,
        'contact' => $faker->word,
        'contact_email' => $faker->word,
        'start_date' => $faker->dateTime(),
        'end_date' => $faker->dateTime(),
        'timezone' => $faker->word,
    ];
});
