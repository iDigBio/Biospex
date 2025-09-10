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

namespace App\Livewire;

use Livewire\Component;

class EventTeamManager extends Component
{
    public $teams = [];

    public $event;

    public $errors;

    public function mount($teams = null, $event = null, $errors = null)
    {
        \Log::info('EventTeamManager mount() called', [
            'teams_type' => gettype($teams),
            'teams_count' => is_array($teams) ? count($teams) : (is_object($teams) && method_exists($teams, 'count') ? $teams->count() : 'unknown'),
            'event_id' => $event ? $event->id : 'null',
            'has_errors' => ! empty($errors),
            'session_id' => session()->getId(),
        ]);

        $this->event = $event;
        $this->errors = $errors;

        // Handle both Collection and array inputs
        if ($teams && (is_array($teams) ? count($teams) > 0 : $teams->isNotEmpty())) {
            // If it's already an array (from controller), use as-is
            // If it's a Collection (from tests or elsewhere), transform it
            if (is_array($teams)) {
                $this->teams = $teams;
            } else {
                $this->teams = $teams->map(function ($team) {
                    return [
                        'id' => $team->id ?? null,
                        'title' => $team->title ?? '',
                    ];
                })->toArray();
            }
        } else {
            // Initialize as empty array
            $this->teams = [];
            // Start with at least one empty team
            $this->addTeam();
        }

        \Log::info('EventTeamManager mount() completed', [
            'final_teams_count' => count($this->teams),
            'teams_structure' => $this->teams,
        ]);
    }

    public function addTeam()
    {
        // Add detailed logging to debug the issue
        \Log::info('EventTeamManager addTeam() called', [
            'current_teams_count' => count($this->teams),
            'session_id' => session()->getId(),
            'timestamp' => now(),
        ]);

        // Add a new empty team array
        $newTeam = [
            'id' => null,
            'title' => '',
        ];
        $this->teams[] = $newTeam;

        \Log::info('EventTeamManager addTeam() completed', [
            'new_teams_count' => count($this->teams),
            'added_team' => $newTeam,
        ]);
    }

    public function removeTeam($index)
    {
        if (count($this->teams) > 1) {
            unset($this->teams[$index]);
            $this->teams = array_values($this->teams); // Re-index array
        }
    }

    public function render()
    {
        return view('livewire.event-team-manager');
    }
}
