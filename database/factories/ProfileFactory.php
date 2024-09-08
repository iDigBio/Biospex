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

use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Profile>
 */
final class ProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Profile::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'timezone' => fake()->timezone,
            'avatar_file_name' => fake()->optional()->word,
            'avatar_file_size' => fake()->optional()->randomNumber(),
            'avatar_content_type' => fake()->optional()->word,
            'avatar_updated_at' => fake()->optional()->datetime(),
            'avatar_created_at' => fake()->optional()->datetime(),
        ];
    }
}
