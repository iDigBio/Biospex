<?php
/**
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <79e6ef82@opayq.com>
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

/** Permissions (used in table seeder */
$items = array(
    // superuser
    array(
        'name' => 'superuser',
        'group' => 'superuser',
        'description' => 'pages.superuser',
    ),

    // project
    array(
        'name' => "project_create",
        'group' => "project",
        'description' => "projects.project-create",
    ),
    array(
        'name' => "project_edit",
        'group' => "project",
        'description' => "projects.project-edit",
    ),
    array(
        'name' => "project_view",
        'group' => "project",
        'description' => "projects.project-view",
    ),
    array(
        'name' => "project_delete",
        'group' => "project",
        'description' => "projects.project-delete",
    ),

    // expedition
    array(
        'name' => "expedition_create",
        'group' => "expedition",
        'description' => "pages.expedition-create",
    ),
    array(
        'name' => "expedition_edit",
        'group' => "expedition",
        'description' => "pages.expedition-edit",
    ),
    array(
        'name' => "expedition_view",
        'group' => "expedition",
        'description' => "pages.expedition-view",
    ),
    array(
        'name' => "expedition_delete",
        'group' => "expedition",
        'description' => "pages.expedition-delete",
    ),

    // permissions
    array(
        'name' => "permission_edit",
        'group' => "permission",
        'description' => "pages.permissions-edit",
    ),
    array(
        'name' => "permission_view",
        'group' => "permission",
        'description' => "pages.permissions-view",
    ),

    // user
    array(
        'name' => "user_create",
        'group' => "user",
        'description' => "pages.user-create",
    ),
    array(
        'name' => "user_edit",
        'group' => "user",
        'description' => "pages.user-edit",
    ),
    array(
        'name' => "user_ban",
        'group' => "user",
        'description' => "pages.user-ban",
    ),
    array(
        'name' => "user_view",
        'group' => "user",
        'description' => "pages.user-view",
    ),
    array(
        'name' => "user_delete",
        'group' => "user",
        'description' => "pages.user-delete",
    ),
    array(
        'name' => "user_edit_permissions",
        'group' => "user",
        'description' => "pages.user-edit-permissions",
    ),
    array(
        'name' => "user_edit_groups",
        'group' => "user",
        'description' => "pages.user-edit-groups",
    ),

    // groups
    array(
        'name' => "group_create",
        'group' => "group",
        'description' => "groups.group-create",
    ),
    array(
        'name' => "group_edit",
        'group' => "group",
        'description' => "groups.group-edit",
    ),
    array(
        'name' => "group_view",
        'group' => "group",
        'description' => "groups.group-view",
    ),
    array(
        'name' => "group_delete",
        'group' => "group",
        'description' => "groups.group-delete",
    ),
);