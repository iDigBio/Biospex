<?php
/**
 * UserGroupTableSeeder.php
 *
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
class UserGroupTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users_groups')->truncate();

        $userUser = Sentry::getUserProvider()->findByLogin('biospex@gmail.com');
        $adminUser = Sentry::getUserProvider()->findByLogin('admin@biospex.org');

        $user = Sentry::getGroupProvider()->findByName('Users');
        $herbarium = Sentry::getGroupProvider()->findByName('Herbarium');
        $calbug = Sentry::getGroupProvider()->findByName('Calbug');
        $adminGroup = Sentry::getGroupProvider()->findByName('Admins');

        // Assign the groups to the users
        $userUser->addGroup($user);
        $userUser->addGroup($herbarium);
        $userUser->addGroup($calbug);
        $adminUser->addGroup($adminGroup);
    }

}