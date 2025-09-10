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

use App\Http\Requests\InviteFormRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

describe('InviteFormRequest Feature Tests', function () {
    it('validates email format correctly', function () {
        $request = new InviteFormRequest;
        $rules = $request->rules();

        expect($rules)->toHaveKey('invites.*.email');
        expect($rules['invites.*.email'])->toBe('nullable|email');
    });

    it('accepts valid email addresses', function () {
        $request = new InviteFormRequest;
        $rules = $request->rules();

        $validEmails = [
            'invites' => [
                ['email' => 'test@example.com'],
                ['email' => 'user.name+tag@domain.co.uk'],
                ['email' => 'simple@test.org'],
            ],
        ];

        $validator = Validator::make($validEmails, $rules);

        expect($validator->passes())->toBeTrue();
    });

    it('rejects invalid email addresses', function () {
        $request = new InviteFormRequest;
        $rules = $request->rules();

        $invalidEmails = [
            'invites' => [
                ['email' => 'not-an-email'],
                ['email' => '@domain.com'],
                ['email' => 'user@'],
                ['email' => 'user name@domain.com'],
            ],
        ];

        $validator = Validator::make($invalidEmails, $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('invites.0.email'))->toBeTrue();
        expect($validator->errors()->has('invites.1.email'))->toBeTrue();
        expect($validator->errors()->has('invites.2.email'))->toBeTrue();
        expect($validator->errors()->has('invites.3.email'))->toBeTrue();
    });

    it('allows empty email values to pass validation', function () {
        $request = new InviteFormRequest;
        $rules = $request->rules();

        $emptyEmails = [
            'invites' => [
                ['email' => ''],
                ['email' => 'valid@example.com'],
            ],
        ];

        $validator = Validator::make($emptyEmails, $rules);

        expect($validator->passes())->toBeTrue();
    });

    it('validates multiple invite entries correctly', function () {
        $request = new InviteFormRequest;
        $rules = $request->rules();

        $multipleInvites = [
            'invites' => [
                ['email' => 'user1@example.com'],
                ['email' => 'user2@example.com'],
                ['email' => 'user3@example.com'],
                ['email' => 'invalid-email'],
                ['email' => 'user4@example.com'],
            ],
        ];

        $validator = Validator::make($multipleInvites, $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('invites.3.email'))->toBeTrue();
        expect($validator->errors()->has('invites.0.email'))->toBeFalse();
        expect($validator->errors()->has('invites.1.email'))->toBeFalse();
        expect($validator->errors()->has('invites.2.email'))->toBeFalse();
        expect($validator->errors()->has('invites.4.email'))->toBeFalse();
    });

    it('provides custom error messages', function () {
        $request = new InviteFormRequest;
        $messages = $request->messages();

        expect($messages)->toHaveKey('invites.*.email');
        expect($messages['invites.*.email'])->toBe('Please enter valid email addresses');
    });

    it('uses custom error message in validation failure', function () {
        $request = new InviteFormRequest;
        $rules = $request->rules();
        $messages = $request->messages();

        $invalidData = [
            'invites' => [
                ['email' => 'invalid-email'],
            ],
        ];

        $validator = Validator::make($invalidData, $rules, $messages);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->first('invites.0.email'))->toBe('Please enter valid email addresses');
    });

    it('handles missing invites array gracefully', function () {
        $request = new InviteFormRequest;
        $rules = $request->rules();

        $noInvites = [];

        $validator = Validator::make($noInvites, $rules);

        expect($validator->passes())->toBeTrue();
    });

    it('handles nested array structure correctly', function () {
        $request = new InviteFormRequest;
        $rules = $request->rules();

        $nestedData = [
            'invites' => [
                0 => ['email' => 'test1@example.com'],
                1 => ['email' => 'test2@example.com'],
                2 => ['email' => 'invalid'],
                3 => ['email' => 'test3@example.com'],
            ],
        ];

        $validator = Validator::make($nestedData, $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('invites.2.email'))->toBeTrue();
        expect($validator->errors()->count())->toBe(1);
    });

    it('requires authenticated user for authorization', function () {
        $request = new InviteFormRequest;

        // Test without authentication - should return false
        expect($request->authorize())->toBeFalse();
    });

    it('authorizes authenticated users', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        $request = new InviteFormRequest;
        expect($request->authorize())->toBeTrue();
    });
});
