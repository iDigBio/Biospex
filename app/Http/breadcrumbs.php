<?php
// Group Pages
Breadcrumbs::register('groups', function ($breadcrumbs) {
    $breadcrumbs->push('Groups', route('web.groups.index'));
});

Breadcrumbs::register('web.groups.show', function ($breadcrumbs, $group) {
    $breadcrumbs->parent('groups');
    $breadcrumbs->push($group->title, route('web.groups.show', $group->id));
});

Breadcrumbs::register('web.groups.show.create', function ($breadcrumbs) {
    $breadcrumbs->parent('groups');
    $breadcrumbs->push(Lang::get('pages.create'));
});

Breadcrumbs::register('web.groups.show.edit', function ($breadcrumbs, $group) {
    $breadcrumbs->parent('groups');
    $breadcrumbs->push($group->title, route('web.groups.show', $group->id));
    $breadcrumbs->push(Lang::get('pages.edit'));
});

Breadcrumbs::register('web.groups.show.invite', function ($breadcrumbs, $group) {
    $breadcrumbs->parent('groups');
    $breadcrumbs->push($group->title, route('web.groups.show', $group->id));
    $breadcrumbs->push(Lang::get('pages.invite'));
});


// Project Pages
Breadcrumbs::register('projects', function ($breadcrumbs) {
    $breadcrumbs->push('Projects', route('web.projects.index'));
});

Breadcrumbs::register('projects.title', function ($breadcrumbs, $title) {
    $breadcrumbs->push('Projects', route('web.projects.index'));
    $breadcrumbs->push($title);
});

Breadcrumbs::register('web.projects.show', function ($breadcrumbs, $project) {
    $breadcrumbs->parent('projects');
    $breadcrumbs->push($project->group->title, route('web.groups.show', $project->group->id));
    $breadcrumbs->push($project->title);
});

Breadcrumbs::register('web.projects.show.title', function ($breadcrumbs, $project, $title) {
    $breadcrumbs->parent('projects');
    $breadcrumbs->push($project->group->title, route('web.groups.show', $project->group->id));
    $breadcrumbs->push($project->title, route('web.projects.show', $project->id));
    $breadcrumbs->push($title);
});

// Expedition Pages
Breadcrumbs::register('web.expeditions.show', function ($breadcrumbs, $expedition, $link = false) {
    $breadcrumbs->parent('projects');
    $breadcrumbs->push($expedition->project->group->title, route('web.groups.show', $expedition->project->group->id));
    $breadcrumbs->push($expedition->project->title, route('web.projects.show', $expedition->project->id));
    ($link) ?
        $breadcrumbs->push($expedition->title, route('web.expeditions.show', [$expedition->project->id, $expedition->id]))
            : $breadcrumbs->push($expedition->title);
});

Breadcrumbs::register('web.expeditions.show.title', function ($breadcrumbs, $expedition, $title) {
    $breadcrumbs->parent('web.expeditions.show', $expedition, true);
    $breadcrumbs->push($title);
});
