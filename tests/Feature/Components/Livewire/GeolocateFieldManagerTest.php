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

use App\Livewire\GeolocateFieldManager;
use Livewire\Livewire;

test('component can be instantiated', function () {
    $component = Livewire::test(GeolocateFieldManager::class);
    $component->assertStatus(200);
});

test('component mounts with default required fields', function () {
    $component = Livewire::test(GeolocateFieldManager::class);

    $component
        ->assertSet('entries', 5)
        ->assertCount('fields', 5)
        ->assertSet('fields.0.geo', 'County')
        ->assertSet('fields.0.csv', '')
        ->assertSet('fields.1.geo', 'Country')
        ->assertSet('fields.1.csv', '')
        ->assertSet('fields.2.geo', 'Locality')
        ->assertSet('fields.2.csv', '')
        ->assertSet('fields.3.geo', 'ScientificName')
        ->assertSet('fields.3.csv', '')
        ->assertSet('fields.4.geo', 'StateProvince')
        ->assertSet('fields.4.csv', '');
});

test('component mounts with existing fields', function () {
    $existingFields = [
        ['geo' => 'Country', 'csv' => 'country_column'],
        ['geo' => 'StateProvince', 'csv' => 'state_column'],
    ];

    $component = Livewire::test(GeolocateFieldManager::class, [
        'existingFields' => $existingFields,
    ]);

    $component
        ->assertSet('entries', 2)
        ->assertCount('fields', 2)
        ->assertSet('fields.0.geo', 'Country')
        ->assertSet('fields.0.csv', 'country_column')
        ->assertSet('fields.1.geo', 'StateProvince')
        ->assertSet('fields.1.csv', 'state_column');
});

test('addField method adds new empty field', function () {
    $component = Livewire::test(GeolocateFieldManager::class);

    $component
        ->call('addField')
        ->assertSet('entries', 6)
        ->assertCount('fields', 6)
        ->assertSet('fields.5.geo', '')
        ->assertSet('fields.5.csv', '');
});

test('removeField method removes last field', function () {
    $component = Livewire::test(GeolocateFieldManager::class);

    // Add a field first
    $component->call('addField');
    $component->assertCount('fields', 6);

    // Remove a field
    $component
        ->call('removeField')
        ->assertSet('entries', 5)
        ->assertCount('fields', 5);
});

test('removeField does not remove when at minimum required fields', function () {
    $component = Livewire::test(GeolocateFieldManager::class);

    $component
        ->call('removeField')
        ->assertSet('entries', 4)
        ->assertCount('fields', 4);
});

test('checkDuplicates returns true for duplicate geo fields', function () {
    $component = Livewire::test(GeolocateFieldManager::class);

    $component->set('fields', [
        ['geo' => 'Country', 'csv' => 'col1'],
        ['geo' => 'Country', 'csv' => 'col2'],
    ]);

    expect($component->instance()->checkDuplicates())->toBeTrue();
});

test('checkDuplicates returns false for unique geo fields', function () {
    $component = Livewire::test(GeolocateFieldManager::class);

    $component->set('fields', [
        ['geo' => 'Country', 'csv' => 'col1'],
        ['geo' => 'StateProvince', 'csv' => 'col2'],
    ]);

    expect($component->instance()->checkDuplicates())->toBeFalse();
});

test('checkRequiredValues returns missing required fields', function () {
    $component = Livewire::test(GeolocateFieldManager::class);

    $component->set('fields', [
        ['geo' => 'Country', 'csv' => 'col1'],
    ]);

    $missing = $component->instance()->checkRequiredValues();
    expect($missing)->toContain('County', 'Locality', 'ScientificName', 'StateProvince');
    expect($missing)->not->toContain('Country');
});

test('validateFields returns false for duplicate fields', function () {
    $component = Livewire::test(GeolocateFieldManager::class);

    $component->set('fields', [
        ['geo' => 'Country', 'csv' => 'col1'],
        ['geo' => 'Country', 'csv' => 'col2'],
    ]);

    expect($component->instance()->validateFields())->toBeFalse();
    $component->assertSet('errorMessage', 'GeoLocate Export fields may not contain duplicate values.');
});

test('validateFields returns false for missing required fields', function () {
    $component = Livewire::test(GeolocateFieldManager::class);

    $component->set('fields', [
        ['geo' => 'Country', 'csv' => 'col1'],
    ]);

    expect($component->instance()->validateFields())->toBeFalse();
    expect($component->get('errorMessage'))->toContain('GeoLocate requires the fields:');
});

test('validateFields returns true for valid fields', function () {
    $component = Livewire::test(GeolocateFieldManager::class);

    $component->set('fields', [
        ['geo' => 'Country', 'csv' => 'col1'],
        ['geo' => 'County', 'csv' => 'col2'],
        ['geo' => 'Locality', 'csv' => 'col3'],
        ['geo' => 'ScientificName', 'csv' => 'col4'],
        ['geo' => 'StateProvince', 'csv' => 'col5'],
    ]);

    expect($component->instance()->validateFields())->toBeTrue();
    $component->assertSet('errorMessage', '');
});

test('resetComponent resets to required fields', function () {
    $component = Livewire::test(GeolocateFieldManager::class);

    // Add fields and set error message
    $component->call('addField');
    $component->set('errorMessage', 'Some error');

    // Reset component
    $component
        ->call('resetComponent')
        ->assertSet('entries', 5)
        ->assertCount('fields', 5)
        ->assertSet('fields.0.geo', 'County')
        ->assertSet('fields.0.csv', '')
        ->assertSet('errorMessage', '');
});

test('component responds to resetGeolocateFields event', function () {
    $component = Livewire::test(GeolocateFieldManager::class);

    // Add fields
    $component->call('addField');
    $component->assertCount('fields', 6);

    // Emit reset event
    $component
        ->dispatch('resetGeolocateFields')
        ->assertSet('entries', 5)
        ->assertCount('fields', 5);
});
