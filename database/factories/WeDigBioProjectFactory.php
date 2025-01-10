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

use App\Models\WeDigBioProject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\WeDigBioProject>
 */
final class WeDigBioProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WeDigBioProject::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'panoptes_project_id' => fake()->randomNumber(),
            'panoptes_workflow_id' => fake()->randomNumber(),
            'subject_sets' => fake()->optional()->text,
            'title' => fake()->title,
        ];
    }
}
