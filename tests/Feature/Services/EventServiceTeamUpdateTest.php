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

use App\Models\Event;
use App\Models\EventTeam;
use App\Models\Project;
use App\Models\User;
use App\Services\Event\EventService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->project = Project::factory()->create();
    $this->eventService = app(EventService::class);
});

describe('EventService Team Update Tests', function () {

    it('can update event with new team that has no id key', function () {
        $event = Event::factory()->create([
            'project_id' => $this->project->id,
            'owner_id' => $this->user->id,
            'timezone' => 'America/New_York',
        ]);

        // Simulate form data with new team that has no 'id' key
        $attributes = [
            'title' => $event->title,
            'description' => $event->description,
            'contact' => $event->contact,
            'contact_email' => $event->contact_email,
            'start_date' => $event->start_date->format('Y-m-d H:i'),
            'end_date' => $event->end_date->format('Y-m-d H:i'),
            'timezone' => $event->timezone,
            'teams' => [
                ['title' => 'New Team Name'], // No 'id' key - this should cause the error
            ],
        ];

        // This should now work without throwing an error
        $result = $this->eventService->update($attributes, $event);

        expect($result)->toBeTrue();
        $event->refresh();
        expect($event->teams)->toHaveCount(1);
        expect($event->teams->first()->title)->toBe('New Team Name');
    });

    it('works with existing team that has id', function () {
        $event = Event::factory()->create([
            'project_id' => $this->project->id,
            'owner_id' => $this->user->id,
            'timezone' => 'America/New_York',
        ]);

        $existingTeam = EventTeam::factory()->create([
            'event_id' => $event->id,
            'title' => 'Original Team',
        ]);

        // Form data with existing team that has 'id' key
        $attributes = [
            'title' => $event->title,
            'description' => $event->description,
            'contact' => $event->contact,
            'contact_email' => $event->contact_email,
            'start_date' => $event->start_date->format('Y-m-d H:i'),
            'end_date' => $event->end_date->format('Y-m-d H:i'),
            'timezone' => $event->timezone,
            'teams' => [
                ['id' => $existingTeam->id, 'title' => 'Updated Team Name'],
            ],
        ];

        $result = $this->eventService->update($attributes, $event);

        expect($result)->toBeTrue();
        $existingTeam->refresh();
        expect($existingTeam->title)->toBe('Updated Team Name');
    });

    it('handles mixed teams with and without id keys', function () {
        $event = Event::factory()->create([
            'project_id' => $this->project->id,
            'owner_id' => $this->user->id,
            'timezone' => 'America/New_York',
        ]);

        $existingTeam = EventTeam::factory()->create([
            'event_id' => $event->id,
            'title' => 'Existing Team',
        ]);

        // Mixed form data: one existing team with id, one new team without id
        $attributes = [
            'title' => $event->title,
            'description' => $event->description,
            'contact' => $event->contact,
            'contact_email' => $event->contact_email,
            'start_date' => $event->start_date->format('Y-m-d H:i'),
            'end_date' => $event->end_date->format('Y-m-d H:i'),
            'timezone' => $event->timezone,
            'teams' => [
                ['id' => $existingTeam->id, 'title' => 'Updated Existing Team'],
                ['title' => 'Brand New Team'], // No 'id' key
            ],
        ];

        // This should now work correctly with mixed teams
        $result = $this->eventService->update($attributes, $event);

        expect($result)->toBeTrue();
        $event->refresh();
        expect($event->teams)->toHaveCount(2);
        expect($event->teams->where('title', 'Updated Existing Team'))->toHaveCount(1);
        expect($event->teams->where('title', 'Brand New Team'))->toHaveCount(1);
    });
});
