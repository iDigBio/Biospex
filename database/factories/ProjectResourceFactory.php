<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\ProjectResource::class, function (Faker $faker) {
    return [
        'project_id' => factory(App\Models\Project::class),
        'type' => $faker->word,
        'name' => $faker->name,
        'description' => $faker->text,
        'download_file_name' => $faker->word,
        'download_file_size' => $faker->randomNumber(),
        'download_content_type' => $faker->word,
        'download_updated_at' => $faker->dateTime(),
    ];
});
