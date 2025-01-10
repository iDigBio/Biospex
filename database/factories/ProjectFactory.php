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

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Project>
 */
final class ProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Project::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->optional()->uuid,
            'group_id' => \App\Models\Group::factory(),
            'title' => fake()->words(2, true),
            'slug' => fake()->slug(2),
            'contact' => fake()->optional()->word,
            'contact_email' => fake()->optional()->word,
            'contact_title' => fake()->optional()->word,
            'organization_website' => fake()->optional()->word,
            'organization' => fake()->optional()->word,
            'project_partners' => fake()->optional()->text,
            'funding_source' => fake()->optional()->text,
            'description_short' => fake()->optional()->word,
            'description_long' => fake()->optional()->text,
            'incentives' => fake()->optional()->text,
            'geographic_scope' => fake()->optional()->word,
            'taxonomic_scope' => fake()->optional()->word,
            'temporal_scope' => fake()->optional()->word,
            'keywords' => fake()->optional()->word,
            'blog_url' => fake()->optional()->word,
            'facebook' => fake()->optional()->word,
            'twitter' => fake()->optional()->word,
            'activities' => fake()->optional()->word,
            'language_skills' => fake()->optional()->word,
            'logo_file_name' => fake()->optional()->word,
            'logo_file_size' => fake()->optional()->randomNumber(),
            'logo_content_type' => fake()->optional()->word,
            'logo_updated_at' => fake()->optional()->datetime(),
            'banner_file' => fake()->optional()->word,
            'target_fields' => null,
            'advertise' => null,
        ];
    }
}
