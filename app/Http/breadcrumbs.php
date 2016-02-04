<?php

Breadcrumbs::register('projects', function ($breadcrumbs) {
    $breadcrumbs->push('Dashboard', route('projects.get.index'));
});

Breadcrumbs::register('groups', function ($breadcrumbs) {
    $breadcrumbs->push('Groups', route('groups.get.index'));
});

Breadcrumbs::register('groups.get.read-with-link', function ($breadcrumbs) {
    $breadcrumbs->push('Groups', route('groups.get.index'));
    $breadcrumbs->push('');
});

Breadcrumbs::register('groups.get.read', function ($breadcrumbs, $group) {
    $breadcrumbs->parent('groups');
    $breadcrumbs->push($group->label, route('groups.get.read', $group->id));
});

Breadcrumbs::register('projects.get.read', function ($breadcrumbs, $project) {
    $breadcrumbs->parent('groups.get.read', $project->group);
    $breadcrumbs->push($project->title);
});

Breadcrumbs::register('projects.get.read-with-link', function ($breadcrumbs, $project) {
    $breadcrumbs->parent('groups.get.read', $project->group);
    $breadcrumbs->push($project->title, route('projects.get.read', $project->id));
});

Breadcrumbs::register('projects.inside', function ($breadcrumbs, $project) {
    $breadcrumbs->parent('projects.get.read-with-link', $project);
    $breadcrumbs->push('');
});

Breadcrumbs::register('projects.get.create', function ($breadcrumbs) {
    $breadcrumbs->parent('projects');
    $breadcrumbs->push(Lang::get('pages.create'));
});

Breadcrumbs::register('projects.expeditions.get.read', function ($breadcrumbs, $expedition) {
    $breadcrumbs->parent('projects.get.read-with-link', $expedition->project);
    $breadcrumbs->push($expedition->title);
});

Breadcrumbs::register('projects.expeditions.show-with-link', function ($breadcrumbs, $expedition) {
    $breadcrumbs->parent('projects.get.read-with-link', $expedition->project);
    $breadcrumbs->push($expedition->title, route('projects.expeditions.get.read', [$expedition->project->id, $expedition->id]));
});

Breadcrumbs::register('projects.expeditions.get.create', function ($breadcrumbs, $project) {
    $breadcrumbs->parent('projects.get.read-with-link', $project);
    $breadcrumbs->push('');
});

Breadcrumbs::register('projects.expeditions.inside', function ($breadcrumbs, $expedition) {
    $breadcrumbs->parent('projects.expeditions.show-with-link', $expedition);
    $breadcrumbs->push('');
});

Breadcrumbs::register('projects.expeditions.inside', function ($breadcrumbs, $expedition) {
    $breadcrumbs->parent('projects.expeditions.show-with-link', $expedition);
    $breadcrumbs->push('');
});

Breadcrumbs::register('projects.subjects', function ($breadcrumbs, $project) {
    $breadcrumbs->parent('projects.get.read-with-link', $project);
    $breadcrumbs->push('Subjects');
});
