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

use App\Livewire\GroupInviteManager;
use App\Models\Group;
use App\Models\GroupInvite;
use App\Models\User;
use App\Notifications\GroupInvite as GroupInviteNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

describe('Group Invite Modal Access Tests', function () {
    it('shows invite modal for group owner', function () {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $group = Group::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->get(route('admin.invites.create', $group));

        $response->assertStatus(200)
            ->assertViewIs('admin.partials.invite-modal-body')
            ->assertViewHas('pass', true)
            ->assertViewHas('group', $group);
    });

    it('denies access to non-owner users', function () {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $nonOwner = User::factory()->create(['email_verified_at' => now()]);
        $group = Group::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($nonOwner)
            ->get(route('admin.invites.create', $group));

        $response->assertStatus(200)
            ->assertViewHas('pass', false);
    });

    it('loads existing invites in modal', function () {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $group = Group::factory()->create(['user_id' => $user->id]);
        $invite = GroupInvite::factory()->create([
            'group_id' => $group->id,
            'email' => 'test@example.com',
        ]);

        $response = $this->actingAs($user)
            ->get(route('admin.invites.create', $group));

        $response->assertStatus(200)
            ->assertViewHas('group')
            ->assertSee('test@example.com');
    });
});

describe('GroupInviteManager Livewire Component Tests', function () {
    it('mounts with empty invites and adds default invite', function () {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $group = Group::factory()->create(['user_id' => $user->id]);

        $component = Livewire::actingAs($user)
            ->test(GroupInviteManager::class, [
                'invites' => collect([]),
                'group' => $group,
                'errors' => [],
            ]);

        $component->assertSet('invites', function ($invites) {
            return count($invites) === 1 && $invites[0]['email'] === '';
        });
    });

    it('can add new invite fields', function () {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $group = Group::factory()->create(['user_id' => $user->id]);

        $component = Livewire::actingAs($user)
            ->test(GroupInviteManager::class, [
                'invites' => collect([]),
                'group' => $group,
                'errors' => [],
            ]);

        $component->assertCount('invites', 1)
            ->call('addInvite')
            ->assertCount('invites', 2);
    });

    it('can remove invite fields when more than one exists', function () {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $group = Group::factory()->create(['user_id' => $user->id]);

        $component = Livewire::actingAs($user)
            ->test(GroupInviteManager::class, [
                'invites' => collect([]),
                'group' => $group,
                'errors' => [],
            ]);

        $component->call('addInvite')
            ->assertCount('invites', 2)
            ->call('removeInvite', 1)
            ->assertCount('invites', 1);
    });

    it('cannot remove the last invite field', function () {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $group = Group::factory()->create(['user_id' => $user->id]);

        $component = Livewire::actingAs($user)
            ->test(GroupInviteManager::class, [
                'invites' => collect([]),
                'group' => $group,
                'errors' => [],
            ]);

        $component->assertCount('invites', 1)
            ->call('removeInvite', 0)
            ->assertCount('invites', 1);
    });

    it('loads existing invites correctly', function () {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $group = Group::factory()->create(['user_id' => $user->id]);
        $existingInvites = collect([
            (object) ['id' => 1, 'email' => 'test1@example.com'],
            (object) ['id' => 2, 'email' => 'test2@example.com'],
        ]);

        $component = Livewire::actingAs($user)
            ->test(GroupInviteManager::class, [
                'invites' => $existingInvites,
                'group' => $group,
                'errors' => [],
            ]);

        $component->assertCount('invites', 2)
            ->assertSet('invites.0.email', 'test1@example.com')
            ->assertSet('invites.1.email', 'test2@example.com');
    });
});

describe('Group Invite Submission Tests', function () {
    beforeEach(function () {
        Notification::fake();
    });

    it('can submit multiple invites successfully', function () {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $group = Group::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post(route('admin.invites.store', $group), [
                'invites' => [
                    ['email' => 'test1@example.com'],
                    ['email' => 'test2@example.com'],
                ],
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('group_invites', [
            'group_id' => $group->id,
            'email' => 'test1@example.com',
        ]);

        $this->assertDatabaseHas('group_invites', [
            'group_id' => $group->id,
            'email' => 'test2@example.com',
        ]);

        Notification::assertSentTimes(GroupInviteNotification::class, 2);
    });

    it('filters out empty email invites', function () {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $group = Group::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post(route('admin.invites.store', $group), [
                'invites' => [
                    ['email' => 'test@example.com'],
                    ['email' => ''],
                    ['email' => 'test2@example.com'],
                ],
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('group_invites', [
            'group_id' => $group->id,
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('group_invites', [
            'group_id' => $group->id,
            'email' => 'test2@example.com',
        ]);

        $this->assertDatabaseMissing('group_invites', [
            'group_id' => $group->id,
            'email' => '',
        ]);
    });

    it('adds existing users directly to group instead of creating invites', function () {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $group = Group::factory()->create(['user_id' => $user->id]);
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($user)
            ->post(route('admin.invites.store', $group), [
                'invites' => [
                    ['email' => 'existing@example.com'],
                    ['email' => 'new@example.com'],
                ],
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        // Existing user should be added to group
        $this->assertTrue($existingUser->fresh()->hasGroup($group));

        // New invite should be created for non-existing user
        $this->assertDatabaseHas('group_invites', [
            'group_id' => $group->id,
            'email' => 'new@example.com',
        ]);

        // No invite should be created for existing user
        $this->assertDatabaseMissing('group_invites', [
            'group_id' => $group->id,
            'email' => 'existing@example.com',
        ]);
    });

    it('does not duplicate invites for already invited emails', function () {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $group = Group::factory()->create(['user_id' => $user->id]);
        GroupInvite::factory()->create([
            'group_id' => $group->id,
            'email' => 'existing@example.com',
        ]);

        $response = $this->actingAs($user)
            ->post(route('admin.invites.store', $group), [
                'invites' => [
                    ['email' => 'existing@example.com'],
                    ['email' => 'new@example.com'],
                ],
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        // Should only have one invite for existing email
        $this->assertEquals(1, GroupInvite::where([
            'group_id' => $group->id,
            'email' => 'existing@example.com',
        ])->count());

        // Should create new invite for new email
        $this->assertDatabaseHas('group_invites', [
            'group_id' => $group->id,
            'email' => 'new@example.com',
        ]);
    });

    it('validates email format', function () {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $group = Group::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post(route('admin.invites.store', $group), [
                'invites' => [
                    ['email' => 'invalid-email'],
                    ['email' => 'valid@example.com'],
                ],
            ]);

        $response->assertSessionHasErrors('invites.0.email');
    });

    it('requires authentication for invite submission', function () {
        $group = Group::factory()->create();

        $response = $this->post(route('admin.invites.store', $group), [
            'invites' => [['email' => 'test@example.com']],
        ]);

        $response->assertRedirect(route('app.get.login'));
    });

    it('allows any authenticated user to submit invites', function () {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $nonOwner = User::factory()->create(['email_verified_at' => now()]);
        $group = Group::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($nonOwner)
            ->post(route('admin.invites.store', $group), [
                'invites' => [['email' => 'test@example.com']],
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');
    });
});
