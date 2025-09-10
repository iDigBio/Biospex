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

use App\Models\Resource;

describe('Resource Page Basic Tests', function () {
    it('displays the resource page successfully', function () {
        $response = $this->get(route('front.resources.index'));

        $response->assertStatus(200);
    });

    it('returns the correct view for resource page', function () {
        $response = $this->get(route('front.resources.index'));

        $response->assertViewIs('front.resource.index');
    });
});

describe('Resource Content Tests', function () {
    it('displays resource titles when available', function () {
        Resource::factory()->count(3)->create();
        $titles = Resource::all()->pluck('title')->toArray();

        $response = $this->get(route('front.resources.index'));

        foreach ($titles as $title) {
            $response->assertSee($title);
        }
    });

    it('displays resource content when available', function () {
        $resources = Resource::factory()->count(2)->create();

        $response = $this->get(route('front.resources.index'));

        foreach ($resources as $resource) {
            $response->assertSee($resource->title);
            if ($resource->description) {
                $response->assertSee($resource->description);
            }
        }
    });
});
