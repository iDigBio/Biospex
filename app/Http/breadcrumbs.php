<?php
// Group Pages
Breadcrumbs::register('groups', function ($breadcrumbs) {
    $breadcrumbs->push('Groups', route('webauth.groups.index'));
});

Breadcrumbs::register('webauth.groups.show', function ($breadcrumbs, $group) {
    $breadcrumbs->parent('groups');
    $breadcrumbs->push($group->title, route('webauth.groups.show', $group->id));
});

Breadcrumbs::register('webauth.groups.show.create', function ($breadcrumbs) {
    $breadcrumbs->parent('groups');
    $breadcrumbs->push(Lang::get('pages.create'));
});

Breadcrumbs::register('webauth.groups.show.edit', function ($breadcrumbs, $group) {
    $breadcrumbs->parent('groups');
    $breadcrumbs->push($group->title, route('webauth.groups.show', $group->id));
    $breadcrumbs->push(Lang::get('pages.edit'));
});

Breadcrumbs::register('webauth.groups.show.invite', function ($breadcrumbs, $group) {
    $breadcrumbs->parent('groups');
    $breadcrumbs->push($group->title, route('webauth.groups.show', $group->id));
    $breadcrumbs->push(Lang::get('pages.invite'));
});


// Project Pages
Breadcrumbs::register('projects', function ($breadcrumbs) {
    $breadcrumbs->push('Projects', route('webauth.projects.index'));
});

Breadcrumbs::register('projects.title', function ($breadcrumbs, $title) {
    $breadcrumbs->push('Projects', route('webauth.projects.index'));
    $breadcrumbs->push($title);
});

Breadcrumbs::register('webauth.projects.show', function ($breadcrumbs, $project) {
    $breadcrumbs->parent('projects');
    $breadcrumbs->push($project->group->title, route('webauth.groups.show', $project->group->id));
    $breadcrumbs->push($project->title);
});

Breadcrumbs::register('webauth.projects.show.title', function ($breadcrumbs, $project, $title) {
    $breadcrumbs->parent('projects');
    $breadcrumbs->push($project->group->title, route('webauth.groups.show', $project->group->id));
    $breadcrumbs->push($project->title, route('webauth.projects.show', $project->id));
    $breadcrumbs->push($title);
});

// Expedition Pages
Breadcrumbs::register('webauth.expeditions.show', function ($breadcrumbs, $expedition, $link = false) {
    $breadcrumbs->parent('projects');
    $breadcrumbs->push($expedition->project->group->title, route('webauth.groups.show', $expedition->project->group->id));
    $breadcrumbs->push($expedition->project->title, route('webauth.projects.show', $expedition->project->id));
    ($link) ?
        $breadcrumbs->push($expedition->title, route('webauth.expeditions.show', [$expedition->project->id, $expedition->id]))
            : $breadcrumbs->push($expedition->title);
});

Breadcrumbs::register('webauth.expeditions.show.title', function ($breadcrumbs, $expedition, $title) {
    $breadcrumbs->parent('webauth.expeditions.show', $expedition, true);
    $breadcrumbs->push($title);
});

// Transcription Pages
Breadcrumbs::register('web.transcriptions.show.title', function ($breadcrumbs, $expedition, $title) {
    $breadcrumbs->parent('webauth.expeditions.show', $expedition, true);
    $breadcrumbs->push($title);
});


// Events Pages
Breadcrumbs::register('webauth.events.index', function ($breadcrumbs) {
    $breadcrumbs->push('Events', route('webauth.events.index'));
});

Breadcrumbs::register('webauth.events.show', function ($breadcrumbs, $event) {
    $breadcrumbs->parent('webauth.events.index');
    $breadcrumbs->push($event->title);
});