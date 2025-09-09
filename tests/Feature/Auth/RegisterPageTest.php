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

use App\Models\Group;
use App\Models\GroupInvite;
use App\Models\User;
use Illuminate\Support\Facades\Http;

describe('Register Page Basic Tests', function () {
    it('displays the register page successfully', function () {
        $response = $this->get(route('app.get.register'));

        $response->assertStatus(200);
    });

    it('returns the correct view for registration', function () {
        $response = $this->get(route('app.get.register'));

        $response->assertViewIs('auth.register');
    });

    it('displays all required form fields', function () {
        $response = $this->get(route('app.get.register'))
            ->assertSee('Register Account')
            ->assertSee('Register')
            ->assertSee('First Name')
            ->assertSee('Last Name')
            ->assertSee('Email')
            ->assertSee('Password')
            ->assertSee('Confirm Password')
            ->assertSee('Timezone');
    });
});

describe('User Registration Tests', function () {
    it('cannot register new user with invalid recaptcha', function () {
        Http::fake([
            config('services.recaptcha.url') => Http::response(['success' => false]),
        ]);

        $firstName = fake()->firstName;
        $lastName = fake()->lastName;
        $email = fake()->email;
        $password = fake()->password;
        $timezone = fake()->timezone;

        $response = $this->post(route('app.post.register'), [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'timezone' => $timezone,
            'g-recaptcha-response' => 'INVALID_RESPONSE',
        ])->assertRedirect('/');

        $this->assertDatabaseMissing('users', [
            'email' => $email,
        ]);
    });

    it('can register new user successfully', function () {
        Http::fake([
            config('services.recaptcha.url') => Http::response(['success' => true]),
        ]);

        $firstName = fake()->firstName;
        $lastName = fake()->lastName;
        $email = fake()->email;
        $password = fake()->password;
        $timezone = fake()->timezone;

        $response = $this->post(route('app.post.register'), [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'timezone' => $timezone,
            'g-recaptcha-response' => 'VALID_RESPONSE',
        ])->assertRedirect('email/verify');

        $this->assertDatabaseHas('users', [
            'email' => $email,
        ]);

        $this->assertDatabaseHas('profiles', [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'timezone' => $timezone,
        ]);

        $this->assertAuthenticated();
    });
});

describe('Group Invite Registration Tests', function () {
    it('displays register page with group invite code in URL', function () {
        $group = Group::factory()->create();
        $invite = GroupInvite::create([
            'group_id' => $group->id,
            'email' => 'invited@example.com',
            'code' => 'TEST-INVITE-CODE',
        ]);

        $response = $this->get(route('app.get.register', $invite));

        $response->assertStatus(200)
            ->assertViewIs('auth.register')
            ->assertViewHas('invite', $invite)
            ->assertSee('invited@example.com'); // Email should be pre-filled
    });

    it('can register new user with valid group invite', function () {
        Http::fake([
            config('services.recaptcha.url') => Http::response(['success' => true]),
        ]);

        $group = Group::factory()->create();
        $invite = GroupInvite::create([
            'group_id' => $group->id,
            'email' => 'invited@example.com',
            'code' => 'TEST-INVITE-CODE',
        ]);

        $firstName = fake()->firstName;
        $lastName = fake()->lastName;
        $password = fake()->password;
        $timezone = fake()->timezone;

        $response = $this->post(route('app.post.register', $invite), [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $invite->email, // Must match invite email
            'password' => $password,
            'password_confirmation' => $password,
            'timezone' => $timezone,
            'g-recaptcha-response' => 'VALID_RESPONSE',
        ])->assertRedirect('email/verify');

        $this->assertDatabaseHas('users', [
            'email' => $invite->email,
        ]);

        $user = User::where('email', $invite->email)->first();

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'timezone' => $timezone,
        ]);

        // User should be added to the group
        $this->assertDatabaseHas('group_user', [
            'group_id' => $group->id,
            'user_id' => $user->id,
        ]);

        $this->assertAuthenticated();
    });

    it('cannot register with group invite when email does not match', function () {
        Http::fake([
            config('services.recaptcha.url') => Http::response(['success' => true]),
        ]);

        $group = Group::factory()->create();
        $invite = GroupInvite::create([
            'group_id' => $group->id,
            'email' => 'invited@example.com',
            'code' => 'TEST-INVITE-CODE',
        ]);

        $firstName = fake()->firstName;
        $lastName = fake()->lastName;
        $differentEmail = 'different@example.com';
        $password = fake()->password;
        $timezone = fake()->timezone;

        $response = $this->post(route('app.post.register', $invite), [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $differentEmail, // Different email from invite
            'password' => $password,
            'password_confirmation' => $password,
            'timezone' => $timezone,
            'g-recaptcha-response' => 'VALID_RESPONSE',
        ])->assertRedirect(route('app.get.register', $invite))
            ->assertSessionHas('danger', 'Email does not match invite email.');

        $this->assertDatabaseMissing('users', [
            'email' => $differentEmail,
        ]);

        $this->assertGuest();
    });

    it('handles invalid group invite gracefully', function () {
        // Test with non-existent invite UUID - should return 404 as expected
        $fakeUuid = '12345678-1234-1234-1234-123456789012';

        $response = $this->get("/register/{$fakeUuid}");

        // Laravel returns 404 when model binding fails, which is the correct behavior
        $response->assertStatus(404);
    });
});
