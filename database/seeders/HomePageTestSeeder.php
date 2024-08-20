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

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Expedition;
use App\Models\ExpeditionStat;
use App\Models\Group;
use App\Models\PanoptesProject;
use App\Models\PanoptesTranscription;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class HomePageTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['id' => 13, 'group_id' => $group->id]);
        Event::factory()->count(5)->create(['project_id' => $project->id]);
        Expedition::factory()
            ->has(ExpeditionStat::factory(), 'stat')
            ->has(PanoptesProject::factory(['project_id' => $project->id]), 'panoptesProject')
            ->count(5)->create(['project_id' => $project->id]);
        PanoptesTranscription::factory()->count(5)->create(['subject_projectId' => $project->id]);
    }
}
