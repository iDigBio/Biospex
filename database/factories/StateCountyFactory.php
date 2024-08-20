<?php
/*
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StateCounty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\StateCounty>
 */
final class StateCountyFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = StateCounty::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'county_name' => fake()->optional()->word,
            'state_county' => fake()->optional()->word,
            'state_abbr' => fake()->optional()->word,
            'state_abbr_cap' => fake()->optional()->word,
            'geometry' => fake()->optional()->text,
            'value' => fake()->optional()->word,
            'geo_id' => fake()->optional()->word,
            'geo_id_2' => fake()->optional()->word,
            'geographic_name' => fake()->optional()->word,
            'state_num' => fake()->optional()->word,
            'county_num' => fake()->optional()->word,
            'fips_forumla' => fake()->optional()->word,
            'has_error' => fake()->optional()->word,
        ];
    }
}
