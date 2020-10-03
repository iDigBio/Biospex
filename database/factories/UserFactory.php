<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\User::class, function (Faker $faker) {
    return [
        'uuid' => $faker->uuid,
        'email' => $faker->safeEmail,
        'password' => bcrypt($faker->password),
        'email_verified_at' => $faker->dateTime(),
        'notification' => $faker->boolean,
        'remember_token' => Str::random(10),
    ];
});
