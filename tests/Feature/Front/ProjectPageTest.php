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

it('has projectpage page', function () {
    $response = $this->get(route('front.projects.index'));

    $response->assertStatus(200);
});

it('returns correct view', function () {
    $response = $this->get(route('front.projects.index'));

    $response->assertViewIs('front.project.index');
});

it('shows list of projects', function () {
    $this->seed(ProjectPageTestSeeder::class);
    $projects = \App\Models\Project::all();
    $this->assertCount(10, $projects->toArray());

    $titles = $projects->pluck('title')->toArray();

    $response = $this->get(route('front.projects.index'));

    foreach ($titles as $title) {
        $response->assertSee($title);
    }

});

it('can sort projects in asc order by title', function () {
    $response = $this->post(route('front.projects.sort'), [
        'sort' => 'title',
        'order' => 'asc',
    ]);

    $response->assertSuccessful();
});

it('can sort projects in desc order by title', function () {
    $response = $this->post(route('front.projects.sort'), [
        'sort' => 'title',
        'order' => 'desc',
    ]);

    $response->assertSuccessful();
});

it('can sort projects in asc order by group', function () {
    $response = $this->post(route('front.projects.sort'), [
        'sort' => 'group',
        'order' => 'asc',
    ]);

    $response->assertSuccessful();
});

it('can sort projects in desc order by group', function () {
    $response = $this->post(route('front.projects.sort'), [
        'sort' => 'group',
        'order' => 'desc',
    ]);

    $response->assertSuccessful();
});

it('can sort projects in asc order by date', function () {
    $response = $this->post(route('front.projects.sort'), [
        'sort' => 'date',
        'order' => 'asc',
    ]);

    $response->assertSuccessful();
});

it('can sort projects in desc order by date', function () {
    $response = $this->post(route('front.projects.sort'), [
        'sort' => 'date',
        'order' => 'desc',
    ]);

    $response->assertSuccessful();
});
