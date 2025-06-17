<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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

use App\Models\ExpeditionStat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ExpeditionStat>
 */
final class ExpeditionStatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ExpeditionStat::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'expedition_id' => \App\Models\Expedition::factory(),
            'local_subject_count' => fake()->randomNumber(),
            'subject_count' => fake()->randomNumber(),
            'transcriptions_goal' => fake()->randomNumber(),
            'local_transcriptions_completed' => fake()->randomNumber(),
            'transcriptions_completed' => fake()->randomNumber(),
            'percent_completed' => fake()->randomFloat(2, 0, 100),
        ];
    }
}
