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

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Expedition;
use App\Models\Group;
use App\Models\PanoptesProject;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectPageTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create(['user_id' => $user->id]);
        Project::factory()->count(10)
            ->has(Expedition::factory()->count(3), 'expeditions')
            ->has(Event::factory()->count(4), 'events')
            ->create(['group_id' => $group->id]);
        $expeditions = Expedition::all();
        $expeditions->each(function ($expedition) {
            PanoptesProject::factory()->create([
                'project_id' => $expedition->project_id,
                'expedition_id' => $expedition->id,
            ]);
        });
    }
}
