<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\Meta::class, function (Faker $faker) {
    return [
        'project_id' => factory(App\Models\Project::class),
        'xml' => $faker->word,
    ];
});
