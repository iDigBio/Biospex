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
use App\Services\Event\EventService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->eventService = app(EventService::class);
});

describe('EventService Team Deletion', function () {

    it('reproduces the team deletion issue when teams are removed via UI', function () {
        // Create an event with teams
        $event = Event::factory()->create();

        // Create existing teams in database
        $team1 = EventTeam::factory()->create([
            'event_id' => $event->id,
            'title' => 'Team Alpha',
        ]);
        $team2 = EventTeam::factory()->create([
            'event_id' => $event->id,
            'title' => 'Team Beta',
        ]);
        $team3 = EventTeam::factory()->create([
            'event_id' => $event->id,
            'title' => 'Team Gamma',
        ]);

        // Verify all teams exist in database
        expect($event->teams()->count())->toBe(3);

        // Simulate form submission where team2 was removed via removeTeam() in UI
        // This represents what happens when user clicks removeTeam - the team is missing from submitted data
        $updateData = [
            'title' => $event->title,
            'description' => $event->description,
            'contact' => $event->contact,
            'contact_email' => $event->contact_email,
            'start_date' => $event->start_date->format('Y-m-d H:i'),
            'end_date' => $event->end_date->format('Y-m-d H:i'),
            'timezone' => 'America/New_York', // Fix timezone issue
            'teams' => [
                ['id' => $team1->id, 'title' => 'Team Alpha'], // existing team kept
                ['id' => $team3->id, 'title' => 'Team Gamma'], // existing team kept
                // team2 is missing - this represents the removed team
            ],
        ];

        // Update the event
        $this->eventService->update($updateData, $event);

        // The issue: team2 should be deleted but it persists in database
        $event->refresh();

        // This assertion will fail, demonstrating the issue
        // Currently team2 still exists because it's never processed for deletion
        expect($event->teams()->count())->toBe(2)
            ->and($event->teams()->where('id', $team2->id)->exists())->toBeFalse();
    });

    it('demonstrates teams are only processed if present in submitted data', function () {
        // Create an event with teams
        $event = Event::factory()->create();

        // Create existing teams
        $team1 = EventTeam::factory()->create([
            'event_id' => $event->id,
            'title' => 'Team One',
        ]);
        $team2 = EventTeam::factory()->create([
            'event_id' => $event->id,
            'title' => 'Team Two',
        ]);

        // Track initial count
        $initialCount = $event->teams()->count();
        expect($initialCount)->toBe(2);

        // Submit form data with only one team (second team "removed" from UI)
        $updateData = [
            'title' => $event->title,
            'description' => $event->description,
            'contact' => $event->contact,
            'contact_email' => $event->contact_email,
            'start_date' => $event->start_date->format('Y-m-d H:i'),
            'end_date' => $event->end_date->format('Y-m-d H:i'),
            'timezone' => 'America/New_York', // Fix timezone issue
            'teams' => [
                ['id' => $team1->id, 'title' => 'Team One Updated'],
                // team2 is missing from submitted data
            ],
        ];

        $this->eventService->update($updateData, $event);

        $event->refresh();

        // The problem: team2 still exists in database even though it was "removed"
        expect($event->teams()->count())->toBe(1)
            ->and($event->teams()->where('title', 'Team One Updated')->exists())->toBeTrue()
            ->and($event->teams()->where('id', $team2->id)->exists())->toBeFalse();
    });

});
