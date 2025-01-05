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

it('has register page', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

it('returns correct view', function () {
    $response = $this->get('/register');

    $response->assertViewIs('auth.register');
});

it('has register form', function () {
    $response = $this->get('/register')
        ->assertSee('Register Account')
        ->assertSee('Register')
        ->assertSee('First Name')
        ->assertSee('Last Name')
        ->assertSee('Email')
        ->assertSee('Password')
        ->assertSee('Confirm Password')
        ->assertSee('Timezone')
        ->assertSee('Group Invite Code');
});

// create test where user regisatraion fails
it('can not register new user with invalid recaptcha', function () {
    Illuminate\Support\Facades\Http::fake([
        config('services.recaptcha.url') => Illuminate\Support\Facades\Http::response(['success' => false]),
    ]);

    $firstName = fake()->firstName;
    $lastName = fake()->lastName;
    $email = fake()->email;
    $password = bcrypt(fake()->password);
    $timezone = fake()->timezone;

    $response = $this->post('/register', [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'password' => $password,
        'password_confirmation' => $password,
        'timezone' => $timezone,
        'g-recaptcha-response' => 'VALID_RESPONSE',
    ])->assertRedirect('/');
    //->assertSessionHasErrors('g-recaptcha-response');

    $this->assertDatabaseMissing('users', [
        'email' => 'test@yahoo.com',
    ]);
});

it('can register new user', function () {
    Illuminate\Support\Facades\Http::fake([
        config('services.recaptcha.url') => Illuminate\Support\Facades\Http::response(['success' => true]),
    ]);

    $firstName = fake()->firstName;
    $lastName = fake()->lastName;
    $email = fake()->email;
    $password = bcrypt(fake()->password);
    $timezone = fake()->timezone;

    $response = $this->post('/register', [
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
