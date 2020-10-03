<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\Invite::class, function (Faker $faker) {
    return [
        'group_id' => factory(App\Models\Group::class),
        'email' => $faker->safeEmail,
        'code' => $faker->word,
    ];
});
