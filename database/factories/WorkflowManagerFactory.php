<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\WorkflowManager::class, function (Faker $faker) {
    return [
        'expedition_id' => factory(App\Models\Expedition::class),
        'stopped' => $faker->boolean,
    ];
});
