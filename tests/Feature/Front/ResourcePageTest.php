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

it('has resourcepage page', function () {
    $response = $this->get(route('front.resources.index'));

    $response->assertStatus(200);
});

it('returns correct view', function () {
    $response = $this->get(route('front.resources.index'));

    $response->assertViewIs('front.resource.index');
});

it('shows resource text on page', function () {
    \App\Models\Resource::factory()->count(3)->create();
    $titles = \App\Models\Resource::all()->pluck('title')->toArray();

    $response = $this->get(route('front.resources.index'));

    foreach ($titles as $title) {
        $response->assertSee($title);
    }
});
