<?php
/**
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(\App\Models\Event::class, function (Faker $faker) {

    $start = $faker->dateTimeBetween('-5 days', 'now');

    return [
        'project_id' => 13,
        'owner_id' => 1,
        'title' => $this->faker->words(3, true),
        'description' => $faker->sentence(6),
        'contact' => $faker->name,
        'contact_email' => $faker->unique()->safeEmail,
        'start_date' => $start,
        'end_date' => $faker->dateTimeBetween($start, '+3 weeks'),
        'timezone' => 'America/New_York'
    ];
});