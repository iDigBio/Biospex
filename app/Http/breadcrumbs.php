<?php

Breadcrumbs::register('projects', function ($breadcrumbs) {
    $breadcrumbs->push('Projects', route('projects.index'));
});

Breadcrumbs::register('groups', function ($breadcrumbs) {
    $breadcrumbs->push('Groups', route('groups.index'));
});

Breadcrumbs::register('groups.show-with-link', function ($breadcrumbs) {
    $breadcrumbs->push('Groups', route('groups.index'));
    $breadcrumbs->push('');
});

Breadcrumbs::register('groups.show', function ($breadcrumbs, $group) {
    $breadcrumbs->parent('groups');
    $breadcrumbs->push($group->name, route('groups.show', $group->id));
});

Breadcrumbs::register('projects.show', function ($breadcrumbs, $project) {
    $breadcrumbs->parent('groups.show', $project->group);
    $breadcrumbs->push($project->title);
});

Breadcrumbs::register('projects.show-with-link', function ($breadcrumbs, $project) {
    $breadcrumbs->parent('groups.show', $project->group);
    $breadcrumbs->push($project->title, route('projects.show', $project->id));
});

Breadcrumbs::register('projects.inside', function ($breadcrumbs, $project) {
    $breadcrumbs->parent('projects.show-with-link', $project);
    $breadcrumbs->push('');
});

Breadcrumbs::register('projects.create', function ($breadcrumbs) {
    $breadcrumbs->parent('projects');
    $breadcrumbs->push(Lang::get('pages.create'));
});

Breadcrumbs::register('projects.expeditions.show', function ($breadcrumbs, $expedition) {
    $breadcrumbs->parent('projects.show-with-link', $expedition->project);
    $breadcrumbs->push($expedition->title);
});

Breadcrumbs::register('projects.expeditions.show-with-link', function ($breadcrumbs, $expedition) {
    $breadcrumbs->parent('projects.show-with-link', $expedition->project);
    $breadcrumbs->push($expedition->title, route('projects.expeditions.show', [$expedition->project->id, $expedition->id]));
});

Breadcrumbs::register('projects.expeditions.create', function ($breadcrumbs, $project) {
    $breadcrumbs->parent('projects.show-with-link', $project);
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
    $breadcrumbs->parent('projects.show-with-link', $project);
    $breadcrumbs->push('Subjects');
});
