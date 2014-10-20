<?php
/**
 * breadcrumbs.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <bruhnrp@gmail.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */

Breadcrumbs::register('projects', function ($breadcrumbs)
{
	$breadcrumbs->push('Projects', route('projects.index'));
});

Breadcrumbs::register('projects.show', function ($breadcrumbs, $project)
{
	$breadcrumbs->parent('projects');
	$breadcrumbs->push($project->group->name);
	$breadcrumbs->push($project->title);
});

Breadcrumbs::register('projects.show-link', function ($breadcrumbs, $project)
{
	$breadcrumbs->parent('projects');
	$breadcrumbs->push($project->group->name);
	$breadcrumbs->push($project->title, route('projects.show', $project->id));
});

Breadcrumbs::register('projects.inside', function ($breadcrumbs, $project)
{
	$breadcrumbs->parent('projects.show-link', $project);
	$breadcrumbs->push('');
});

Breadcrumbs::register('projects.create', function ($breadcrumbs)
{
	$breadcrumbs->parent('projects');
	$breadcrumbs->push(Lang::get('pages.create'));
});

Breadcrumbs::register('projects.expeditions.show', function ($breadcrumbs, $expedition)
{
	$breadcrumbs->parent('projects.show-link', $expedition->project);
	$breadcrumbs->push($expedition->title);
});

Breadcrumbs::register('projects.expeditions.show-link', function ($breadcrumbs, $expedition)
{
	$breadcrumbs->parent('projects.show-link', $expedition->project);
	$breadcrumbs->push($expedition->title, route('projects.expeditions.show', [$expedition->project->id, $expedition->id]));
});

Breadcrumbs::register('projects.expeditions.create', function ($breadcrumbs, $project)
{
	$breadcrumbs->parent('projects.show-link', $project);
	$breadcrumbs->push('');
});

Breadcrumbs::register('projects.expeditions.inside', function ($breadcrumbs, $expedition)
{
	$breadcrumbs->parent('projects.expeditions.show-link', $expedition);
	$breadcrumbs->push('');
});

Breadcrumbs::register('projects.expeditions.inside', function ($breadcrumbs, $expedition)
{
	$breadcrumbs->parent('projects.expeditions.show-link', $expedition);
	$breadcrumbs->push('');
});