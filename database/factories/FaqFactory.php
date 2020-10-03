<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\Faq::class, function (Faker $faker) {
    return [
        'faq_category_id' => factory(App\Models\FaqCategory::class),
        'question' => $faker->word,
        'answer' => $faker->word,
    ];
});
