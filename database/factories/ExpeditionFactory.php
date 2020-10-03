<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\Expedition::class, function (Faker $faker) {
    return [
        'uuid' => $faker->uuid,
        'project_id' => factory(App\Models\Project::class),
        'title' => $faker->word,
        'description' => $faker->text,
        'keywords' => $faker->word,
        'logo_file_name' => $faker->word,
        'logo_file_size' => $faker->randomNumber(),
        'logo_content_type' => $faker->word,
        'logo_updated_at' => $faker->dateTime(),
    ];
});
