<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\Resource::class, function (Faker $faker) {
    return [
        'title' => $faker->word,
        'description' => $faker->text,
        'document_file_name' => $faker->word,
        'document_file_size' => $faker->randomNumber(),
        'document_content_type' => $faker->word,
        'document_updated_at' => $faker->dateTime(),
        'order' => $faker->boolean,
    ];
});
