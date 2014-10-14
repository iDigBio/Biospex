<?php
/**
 * UserTableSeeder.php
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
class UserTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		DB::table('users')->truncate();

		$users = $this->getUsers();

		foreach ($users as $user)
		{
			Sentry::getUserProvider()->create($user);
		}
    }

	private function getUsers()
	{
		return array(
			array(
				'email'         => 'admin@biospex.org',
				'password'      => 'biospex',
				'first_name'    => 'Biospex',
				'last_name'     => 'Admin',
				'activated'     => 1,
			),
			array(
				'email'    => 'biospex@gmail.com',
				'password' => 'biospex',
				'first_name'    => 'Robert',
				'last_name'     => 'Bruhn',
				'activated' => 1,
			),
			array(
				'email' => 'nogroup@gmail.com',
				'password' => 'biospex',
				'first_name' => 'No',
				'last_name' => 'Group',
				'activated' => 1,
			),
			array(
				'email'    => 'macadamiatree@gmail.com',
				'password' => 'biospex',
				'first_name'    => 'Austin',
				'last_name'     => 'Mast',
				'activated' => 1,
			),
			array(
				'email'    => 'jspinks@fsu.edu',
				'password' => 'biospex',
				'first_name'    => 'Jeremy',
				'last_name'     => '',
				'activated' => 1,
			),
			array(
				'email'    => 'eellwood@bio.fsu.edu',
				'password' => 'biospex',
				'first_name'    => 'Libby',
				'last_name'     => '',
				'activated' => 1,
			),
			array(
				'email'    => 'griccardi@fsu.edu',
				'password' => 'biospex',
				'first_name'    => 'Greg',
				'last_name'     => '',
				'activated' => 1,
			)
		);
	}
}