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

it('gives redirect response for unauthicated user', function () {
    $user = \App\Models\User::factory()->create();
    $response = $this->get(route('admin.users.edit', [$user]));

    $response->assertStatus(302);
});

it('gives redirect response for unauthorized user', function () {
    $user = \App\Models\User::factory()->create();
    $response = $this->actingAs($user)->get(route('admin.users.edit', [$user]));

    $response->assertStatus(302);
});

it('gives success response for authorized user', function () {
    $user = \App\Models\User::factory()->verified()->create();
    $profile = \App\Models\Profile::factory()->create([$user]);
    $response = $this->actingAs($user)->get(route('admin.users.edit', [$user]));

    $response->assertStatus(200);
});

it('has profile form for authorized user', function () {
    $user = \App\Models\User::factory()->verified()->create();
    $profile = \App\Models\Profile::factory()->create([$user]);
    $response = $this->actingAs($user)->get(route('admin.users.edit', [$user]));
    $response->assertSee('first_name')->assertSee('last_name')->assertSee('email')->assertSee('timezone');
});

it('can update profile for authorized user', function () {
    $user = \App\Models\User::factory()->verified()->create();
    $profile = \App\Models\Profile::factory()->create(['user_id' => $user->id]);

    // Hit page first due to unit test not working well with Redirect::bock().
    $this->actingAs($user)->get(route('admin.users.edit', [$user]));

    $firstName = fake()->firstName;
    $lastName = fake()->lastName;
    $email = fake()->email;
    $timezone = fake()->timezone;

    $response = $this->actingAs($user)->put(route('admin.users.update', [$user]), [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'timezone' => $timezone,
    ]);

    $response->assertRedirect('/admin/users/'.$user->id.'/edit');

    $this->assertDatabaseHas('profiles', [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'timezone' => $timezone,
    ]);

    $this->assertDatabaseHas('users', [
        'email' => $email,
    ]);
});

it('can update password for authorized user', function () {
    $current_password = fake()->password;
    $user = \App\Models\User::factory()->verified()->create(['password' => bcrypt($current_password)]);
    $profile = \App\Models\Profile::factory()->create(['user_id' => $user]);

    // Hit page first due to unit test not working well with Redirect::bock().
    $this->actingAs($user)->get(route('admin.users.edit', [$user]));

    $password = bcrypt(fake()->password);

    $this->actingAs($user)->put(route('admin.users.update', [$user]), [
        'current_password' => $current_password,
        'password' => $password,
        'password_confirmation' => $password,
    ])->assertRedirect('/admin/users/'.$user->id.'/edit');

    $this->assertTrue(\Hash::check($password, $user->password));
});
