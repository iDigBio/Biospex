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

use App\Models\Group;
use Livewire\Component;

class GroupInviteManager extends Component
{
    public $invites = [];

    public ?Group $group;

    public $errors;

    public function mount($invites = null, ?Group $group = null, $errors = null)
    {
        \Log::info('GroupInviteManager mount() called', [
            'invites_type' => gettype($invites),
            'invites_count' => is_array($invites) ? count($invites) : (is_object($invites) && method_exists($invites, 'count') ? $invites->count() : 'unknown'),
            'group_id' => $group ? $group->id : 'null',
            'has_errors' => ! empty($errors),
            'session_id' => session()->getId(),
        ]);

        $this->group = $group;
        $this->errors = $errors;

        // Handle both Collection and array inputs
        if ($invites && (is_array($invites) ? count($invites) > 0 : $invites->isNotEmpty())) {
            // If it's already an array (from controller), use as-is
            // If it's a Collection (from tests or elsewhere), transform it
            if (is_array($invites)) {
                $this->invites = $invites;
            } else {
                $this->invites = $invites->map(function ($invite) {
                    return [
                        'id' => $invite->id ?? null,
                        'email' => $invite->email ?? '',
                    ];
                })->toArray();
            }
        } else {
            // Initialize as empty array
            $this->invites = [];
            // Start with at least one empty invite
            $this->addInvite();
        }

        \Log::info('GroupInviteManager mount() completed', [
            'final_invites_count' => count($this->invites),
            'invites_structure' => $this->invites,
        ]);
    }

    public function addInvite()
    {
        // Add detailed logging to debug the issue
        \Log::info('GroupInviteManager addInvite() called', [
            'current_invites_count' => count($this->invites),
            'session_id' => session()->getId(),
            'timestamp' => now(),
        ]);

        // Add a new empty invite array
        $newInvite = [
            'id' => null,
            'email' => '',
        ];
        $this->invites[] = $newInvite;

        \Log::info('GroupInviteManager addInvite() completed', [
            'new_invites_count' => count($this->invites),
            'added_invite' => $newInvite,
        ]);
    }

    public function removeInvite($index)
    {
        if (count($this->invites) > 1) {
            unset($this->invites[$index]);
            $this->invites = array_values($this->invites); // Re-index array
        }
    }

    public function render()
    {
        return view('livewire.group-invite-manager');
    }
}
