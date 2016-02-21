<?php
// Group Pages
Breadcrumbs::register('groups', function ($breadcrumbs) {
    $breadcrumbs->push('Groups', route('groups.get.index'));
});

Breadcrumbs::register('groups.get.show', function ($breadcrumbs, $group) {
    $breadcrumbs->parent('groups');
    $breadcrumbs->push($group->label, route('groups.get.show', $group->id));
});

Breadcrumbs::register('groups.get.show.create', function ($breadcrumbs) {
    $breadcrumbs->parent('groups');
    $breadcrumbs->push(Lang::get('pages.create'));
});

Breadcrumbs::register('groups.get.show.edit', function ($breadcrumbs, $group) {
    $breadcrumbs->parent('groups');
    $breadcrumbs->push($group->label, route('groups.get.show', $group->id));
    $breadcrumbs->push(Lang::get('pages.edit'));
});

Breadcrumbs::register('groups.get.show.invite', function ($breadcrumbs, $group) {
    $breadcrumbs->parent('groups');
    $breadcrumbs->push($group->label, route('groups.get.show', $group->id));
    $breadcrumbs->push(Lang::get('pages.invite'));
});


// Project Pages
Breadcrumbs::register('projects', function ($breadcrumbs) {
    $breadcrumbs->push('Projects', route('projects.get.index'));
});

Breadcrumbs::register('projects.title', function ($breadcrumbs, $title) {
    $breadcrumbs->push('Projects', route('projects.get.index'));
    $breadcrumbs->push($title);
});

Breadcrumbs::register('projects.get.show', function ($breadcrumbs, $project) {
    $breadcrumbs->parent('projects');
    $breadcrumbs->push($project->group->label, route('groups.get.show', $project->group->id));
    $breadcrumbs->push($project->title);
});

Breadcrumbs::register('projects.get.show.title', function ($breadcrumbs, $project, $title) {
    $breadcrumbs->parent('projects');
    $breadcrumbs->push($project->group->label, route('groups.get.show', $project->group->id));
    $breadcrumbs->push($project->title, route('projects.get.show', $project->id));
    $breadcrumbs->push($title);
});

// Expedition Pages
Breadcrumbs::register('projects.expeditions.get.show', function ($breadcrumbs, $expedition, $link = false) {
    $breadcrumbs->parent('projects');
    $breadcrumbs->push($expedition->project->group->label, route('groups.get.show', $expedition->project->group->id));
    $breadcrumbs->push($expedition->project->title, route('projects.get.show', $expedition->project->id));
    ($link) ?
        $breadcrumbs->push($expedition->title, route('projects.expeditions.get.show', [$expedition->project->id, $expedition->id]))
            : $breadcrumbs->push($expedition->title);
});

Breadcrumbs::register('projects.expeditions.get.show.title', function ($breadcrumbs, $expedition, $title) {
    $breadcrumbs->parent('projects.expeditions.get.show', $expedition, true);
    $breadcrumbs->push($title);
});
