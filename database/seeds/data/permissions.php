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
$items = [
    // superuser
    [
        'name'        => 'superuser',
        'label'       => 'Superuser',
        'description' => 'Administrator has all permissions'
    ],
    // groups
    [
        'name'        => "create-group",
        'label'       => "Create Group",
        'description' => "Enable group to create group."
    ],
    [
        'name'        => "update-group",
        'label'       => "Update Group",
        'description' => "Enable group to update group."
    ],
    [
        'name'        => "read-group",
        'label'       => "Read Group",
        'description' => "Enable group to read group."
    ],
    [
        'name'        => "delete-group",
        'label'       => "Delete Group",
        'description' => "Enable group to delete group."
    ],
    // project
    [
        'name'        => "create-project",
        'label'       => "Create Project",
        'description' => "Enable group to create project."
    ],
    [
        'name'        => "read-project",
        'label'       => "Read Project",
        'description' => "Enable group to view project."
    ],
    [
        'name'        => "update-project",
        'label'       => "Update Project",
        'description' => "Enable group to update project."
    ],
    [
        'name'        => "delete-project",
        'label'       => "Delete Project",
        'description' => "Enable group to delete project."
    ],
    // expedition
    [
        'name'        => "create-expedition",
        'label'       => "Create Expedition",
        'description' => "Enable group to create expedition."
    ],
    [
        'name'        => "read-expedition",
        'label'       => "Read Expedition",
        'description' => "Enable group to read expedition."
    ],
    [
        'name'        => "update-expedition",
        'label'       => "Update Expedition",
        'description' => "Enable group to update expedition"
    ],
    [
        'name'        => "delete-expedition",
        'label'       => "Delete Expedition",
        'description' => "pages.expedition_delete"
    ],
];
