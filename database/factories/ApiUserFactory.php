<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\ApiUser::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->safeEmail,
        'password' => bcrypt($faker->password),
        'email_verified_at' => $faker->dateTime(),
        'reset_password_code' => $faker->word,
        'remember_token' => Str::random(10),
    ];
});
