<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\ExportQueueFile::class, function (Faker $faker) {
    return [
        'queue_id' => factory(App\Models\ExportQueue::class),
        'subject_id' => $faker->word,
        'url' => $faker->url,
        'error' => $faker->boolean,
        'error_message' => $faker->word,
    ];
});
