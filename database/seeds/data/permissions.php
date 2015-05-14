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
        'description' => 'users.superuser',
    ),

    // project
    array(
        'name' => "project_create",
        'group' => "project",
        'description' => "projects.project_create",
    ),
    array(
        'name' => "project_edit",
        'group' => "project",
        'description' => "projects.project_edit",
    ),
    array(
        'name' => "project_view",
        'group' => "project",
        'description' => "projects.project_view",
    ),
    array(
        'name' => "project_delete",
        'group' => "project",
        'description' => "projects.project_delete",
    ),

    // expedition
    array(
        'name' => "expedition_create",
        'group' => "expedition",
        'description' => "pages.expedition_create",
    ),
    array(
        'name' => "expedition_edit",
        'group' => "expedition",
        'description' => "pages.expedition_edit",
    ),
    array(
        'name' => "expedition_view",
        'group' => "expedition",
        'description' => "pages.expedition_view",
    ),
    array(
        'name' => "expedition_delete",
        'group' => "expedition",
        'description' => "pages.expedition_delete",
    ),

    // permissions
    array(
        'name' => "permission_edit",
        'group' => "permission",
        'description' => "pages.permissions_edit",
    ),
    array(
        'name' => "permission_view",
        'group' => "permission",
        'description' => "pages.permissions_view",
    ),

    // user
    array(
        'name' => "user_create",
        'group' => "user",
        'description' => "users.user_create",
    ),
    array(
        'name' => "user_edit",
        'group' => "user",
        'description' => "users.user_edit",
    ),
    array(
        'name' => "user_ban",
        'group' => "user",
        'description' => "users.user_ban",
    ),
    array(
        'name' => "user_view",
        'group' => "user",
        'description' => "users.user_view",
    ),
    array(
        'name' => "user_delete",
        'group' => "user",
        'description' => "users.user_delete",
    ),
    array(
        'name' => "user_edit_permissions",
        'group' => "user",
        'description' => "users.user_edit_permissions",
    ),
    array(
        'name' => "user_edit_groups",
        'group' => "user",
        'description' => "users.user_edit_groups",
    ),

    // groups
    array(
        'name' => "group_create",
        'group' => "group",
        'description' => "groups.group_create",
    ),
    array(
        'name' => "group_edit",
        'group' => "group",
        'description' => "groups.group_edit",
    ),
    array(
        'name' => "group_view",
        'group' => "group",
        'description' => "groups.group_view",
    ),
    array(
        'name' => "group_delete",
        'group' => "group",
        'description' => "groups.group_delete",
    ),
);