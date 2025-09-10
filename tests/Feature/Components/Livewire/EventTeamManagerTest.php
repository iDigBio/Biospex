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

use App\Livewire\EventTeamManager;
use App\Models\Event;
use App\Models\EventTeam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->event = Event::factory()->create();
});

describe('EventTeamManager Component', function () {

    it('mounts with empty teams collection', function () {
        Livewire::test(EventTeamManager::class, [
            'teams' => collect(),
            'event' => $this->event,
            'errors' => null,
        ])
            ->assertStatus(200)
            ->assertSet('teams', [['id' => null, 'title' => '']])
            ->assertSet('event.id', $this->event->id)
            ->assertSet('errors', null);
    });

    it('mounts with existing teams', function () {
        $eventTeam = EventTeam::factory()->create([
            'event_id' => $this->event->id,
            'title' => 'Test Team',
        ]);

        $teams = collect([$eventTeam]);

        Livewire::test(EventTeamManager::class, [
            'teams' => $teams,
            'event' => $this->event,
            'errors' => null,
        ])
            ->assertStatus(200)
            ->assertSet('teams', [[
                'id' => $eventTeam->id,
                'title' => 'Test Team',
            ]]);
    });

    it('mounts with null teams parameter', function () {
        Livewire::test(EventTeamManager::class, [
            'teams' => null,
            'event' => $this->event,
            'errors' => null,
        ])
            ->assertStatus(200)
            ->assertSet('teams', [['id' => null, 'title' => '']])
            ->assertCount('teams', 1);
    });

    it('can add new team', function () {
        Livewire::test(EventTeamManager::class, [
            'teams' => collect(),
            'event' => $this->event,
            'errors' => null,
        ])
            ->call('addTeam')
            ->assertCount('teams', 2)
            ->assertSet('teams.1', ['id' => null, 'title' => '']);
    });

    it('can remove team when more than one exists', function () {
        Livewire::test(EventTeamManager::class, [
            'teams' => collect(),
            'event' => $this->event,
            'errors' => null,
        ])
            ->call('addTeam') // Should have 2 teams now
            ->assertCount('teams', 2)
            ->call('removeTeam', 0)
            ->assertCount('teams', 1);
    });

    it('cannot remove team when only one exists', function () {
        Livewire::test(EventTeamManager::class, [
            'teams' => collect(),
            'event' => $this->event,
            'errors' => null,
        ])
            ->call('removeTeam', 0)
            ->assertCount('teams', 1); // Should still have one team
    });

    it('mounts with array input', function () {
        $teams = [
            ['id' => null, 'title' => 'Team 1'],
            ['id' => null, 'title' => 'Team 2'],
        ];

        Livewire::test(EventTeamManager::class, [
            'teams' => $teams,
            'event' => $this->event,
            'errors' => null,
        ])
            ->assertStatus(200)
            ->assertCount('teams', 2)
            ->assertSet('teams.0.title', 'Team 1')
            ->assertSet('teams.1.title', 'Team 2');
    });

    it('handles errors parameter', function () {
        $errors = ['teams.0.title' => ['The team title is required.']];

        Livewire::test(EventTeamManager::class, [
            'teams' => null,
            'event' => $this->event,
            'errors' => $errors,
        ])
            ->assertStatus(200)
            ->assertSet('errors', $errors);
    });

    it('can mount without event parameter', function () {
        Livewire::test(EventTeamManager::class, [
            'teams' => null,
            'event' => null,
            'errors' => null,
        ])
            ->assertStatus(200)
            ->assertSet('event', null)
            ->assertCount('teams', 1);
    });

    it('renders the correct view', function () {
        Livewire::test(EventTeamManager::class, [
            'teams' => null,
            'event' => $this->event,
            'errors' => null,
        ])
            ->assertViewIs('livewire.event-team-manager');
    });

});

describe('EventTeamManager Component - Create Page Scenarios', function () {

    it('handles create page with no existing teams', function () {
        Livewire::test(EventTeamManager::class, [
            'teams' => null,
            'event' => null,
            'errors' => null,
        ])
            ->assertStatus(200)
            ->assertCount('teams', 1)
            ->assertSet('teams.0', ['id' => null, 'title' => '']);
    });

    it('handles create page with form validation errors', function () {
        $errors = [
            'teams.0.title' => ['The team title field is required.'],
            'teams.1.title' => ['The team title field is required.'],
        ];

        Livewire::test(EventTeamManager::class, [
            'teams' => [
                ['id' => null, 'title' => ''],
                ['id' => null, 'title' => ''],
            ],
            'event' => null,
            'errors' => $errors,
        ])
            ->assertStatus(200)
            ->assertCount('teams', 2)
            ->assertSet('errors', $errors);
    });

    it('can add multiple teams on create page', function () {
        Livewire::test(EventTeamManager::class, [
            'teams' => null,
            'event' => null,
            'errors' => null,
        ])
            ->call('addTeam')
            ->call('addTeam')
            ->assertCount('teams', 3);
    });

    it('can remove teams on create page', function () {
        Livewire::test(EventTeamManager::class, [
            'teams' => null,
            'event' => null,
            'errors' => null,
        ])
            ->call('addTeam')
            ->call('addTeam')
            ->assertCount('teams', 3)
            ->call('removeTeam', 1)
            ->assertCount('teams', 2);
    });

});
