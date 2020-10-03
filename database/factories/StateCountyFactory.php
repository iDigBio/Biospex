<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\StateCounty::class, function (Faker $faker) {
    return [
        'county_name' => $faker->word,
        'state_county' => $faker->word,
        'state_abbr' => $faker->word,
        'state_abbr_cap' => $faker->word,
        'geometry' => $faker->text,
        'value' => $faker->word,
        'geo_id' => $faker->word,
        'geo_id_2' => $faker->word,
        'geographic_name' => $faker->word,
        'state_num' => $faker->word,
        'county_num' => $faker->word,
        'fips_forumla' => $faker->word,
        'has_error' => $faker->word,
    ];
});
