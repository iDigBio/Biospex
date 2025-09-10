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

use App\Models\Group;
use App\Models\GroupInvite;
use App\Models\User;
use App\Services\Group\GroupInviteService;
use Illuminate\Support\Collection;

describe('GroupInviteService Feature Tests', function () {
    beforeEach(function () {
        $this->service = app(GroupInviteService::class);
    });

    it('filters out empty email invites from request', function () {
        $group = Group::factory()->create();
        $group->load('invites'); // Load empty invites collection

        $request = [
            'invites' => [
                ['email' => 'test@example.com'],
                ['email' => ''],
                ['email' => 'test2@example.com'],
                ['email' => '   '], // whitespace only
            ],
        ];

        $result = $this->service->storeInvites($group, $request);

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result->count())->toBe(2);
    });

    it('excludes emails that already have invites', function () {
        $group = Group::factory()->create();
        $existingInvite = \App\Models\GroupInvite::factory()->create([
            'group_id' => $group->id,
            'email' => 'existing@example.com',
        ]);
        $group->load('invites');

        $request = [
            'invites' => [
                ['email' => 'existing@example.com'],
                ['email' => 'new@example.com'],
            ],
        ];

        $result = $this->service->storeInvites($group, $request);

        expect($result->count())->toBe(1);
        expect($result->first()->email)->toBe('new@example.com');
    });

    it('adds existing users directly to group instead of creating invites', function () {
        $group = Group::factory()->create();
        $group->load('invites');

        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $request = [
            'invites' => [
                ['email' => 'existing@example.com'],
                ['email' => 'nonexistent@example.com'],
            ],
        ];

        $result = $this->service->storeInvites($group, $request);

        // Should return only invite for non-existing user
        expect($result->count())->toBe(1);
        expect($result->first()->email)->toBe('nonexistent@example.com');

        // Existing user should be added to group
        expect($existingUser->fresh()->hasGroup($group))->toBeTrue();
    });

    it('does not add existing user to group if already a member', function () {
        $group = Group::factory()->create();
        $group->load('invites');

        $existingUser = User::factory()->create(['email' => 'member@example.com']);
        $existingUser->assignGroup($group);

        $request = [
            'invites' => [
                ['email' => 'member@example.com'],
                ['email' => 'new@example.com'],
            ],
        ];

        $result = $this->service->storeInvites($group, $request);

        // Should return only invite for new user
        expect($result->count())->toBe(1);
        expect($result->first()->email)->toBe('new@example.com');

        // User should still be a member (not duplicated)
        expect($existingUser->fresh()->hasGroup($group))->toBeTrue();
    });

    it('creates new invites for non-existing users', function () {
        $group = Group::factory()->create();
        $group->load('invites');

        $request = [
            'invites' => [
                ['email' => 'new1@example.com'],
                ['email' => 'new2@example.com'],
            ],
        ];

        $result = $this->service->storeInvites($group, $request);

        expect($result->count())->toBe(2);
        expect($result->first())->toBeInstanceOf(GroupInvite::class);
        expect($result->first()->email)->toBe('new1@example.com');
        expect($result->first()->group_id)->toBe($group->id);

        expect($result->last())->toBeInstanceOf(GroupInvite::class);
        expect($result->last()->email)->toBe('new2@example.com');
        expect($result->last()->group_id)->toBe($group->id);
    });

    it('trims whitespace from email addresses', function () {
        $group = Group::factory()->create();
        $group->load('invites');

        $request = [
            'invites' => [
                ['email' => '  test@example.com  '],
            ],
        ];

        $result = $this->service->storeInvites($group, $request);

        expect($result->count())->toBe(1);
        expect($result->first()->email)->toBe('test@example.com');
    });

    it('handles empty invites collection gracefully', function () {
        $group = Group::factory()->create();
        $group->load('invites');

        $request = [
            'invites' => [],
        ];

        $result = $this->service->storeInvites($group, $request);

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result->count())->toBe(0);
    });

    it('handles mixed scenario with existing users, existing invites, and new invites', function () {
        $group = Group::factory()->create();

        // Create existing invite
        $existingInvite = \App\Models\GroupInvite::factory()->create([
            'group_id' => $group->id,
            'email' => 'invited@example.com',
        ]);

        // Create existing user who is already a member
        $memberUser = User::factory()->create(['email' => 'member@example.com']);
        $memberUser->assignGroup($group);

        // Create existing user who is not a member
        $nonMemberUser = User::factory()->create(['email' => 'nonmember@example.com']);

        $group->load('invites');

        $request = [
            'invites' => [
                ['email' => 'invited@example.com'],     // Already has invite - should be excluded
                ['email' => 'member@example.com'],      // Already a member - should be excluded
                ['email' => 'nonmember@example.com'],   // Existing user, not member - should be added to group
                ['email' => 'new@example.com'],         // New user - should create invite
                ['email' => ''],                         // Empty - should be filtered out
            ],
        ];

        $result = $this->service->storeInvites($group, $request);

        // Should only create invite for new user
        expect($result->count())->toBe(1);
        expect($result->first()->email)->toBe('new@example.com');

        // Non-member user should now be added to group
        expect($nonMemberUser->fresh()->hasGroup($group))->toBeTrue();

        // Member user should still be a member
        expect($memberUser->fresh()->hasGroup($group))->toBeTrue();
    });
});
