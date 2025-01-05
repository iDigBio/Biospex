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

it('has about team page', function () {

    $response = $this->get(route('front.teams.index'));

    $response->assertStatus(200);
});

it('returns correct view', function () {
    $response = $this->get(route('front.teams.index'));

    $response->assertViewIs('front.team.index');
});

it('can see team categories and members on team page', function () {
    \App\Models\TeamCategory::factory()->count(3)
        ->has(\App\Models\Team::factory()->count(2))
        ->create();

    $response = $this->get(route('front.teams.index'));

    $names = \App\Models\TeamCategory::all()->pluck('name')->toArray();

    foreach ($names as $name) {
        $response->assertSee($name);
    }

    $response->assertStatus(200);
});
