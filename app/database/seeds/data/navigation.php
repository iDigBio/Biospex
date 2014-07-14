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

/** Navigation used in table seeder */
$items = array(
    // group
    array(
        'type' => "topmenu",
        'name' => "groups.groups",
        'url' => "#",
        'permission' => 'group_view',
        'order' => 1,
        'parent_id' => 0,
        'children' => array(
            array(
                'type' => "topmenu",
                'name' => "pages.manage",
                'url' => "/groups",
                'permission' => 'group_view',
                'order' => 2,
            ),
            array(
                'type' => "topmenu",
                'name' => "pages.create",
                'url' => "/groups/create",
                'permission' => 'group_create',
                'order' => 3,
            ),
        )
    ),
    // project
    array(
        'type' => "topmenu",
        'name' => "projects.projects",
        'url' => "/projects/all",
        'permission' => 'project_view',
        'order' => 1,
        'parent_id' => 0,
    ),
    // user
    array(
        'type' => "topmenu",
        'name' => "users.users",
        'url' => "#",
        'permission' => 'user_view',
        'order' => 1,
        'parent_id' => 0,
        'children' => array(
            array(
                'type' => "topmenu",
                'name' => "pages.list",
                'url' => "/users",
                'permission' => 'user_view',
                'order' => 2,
            ),
            array(
                'type' => "topmenu",
                'name' => "pages.create",
                'url' => "/users/create",
                'permission' => 'user_create',
                'order' => 3,
            ),
        )
    ),
);