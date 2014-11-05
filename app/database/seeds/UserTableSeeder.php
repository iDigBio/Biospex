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
use Biospex\Repo\User\UserInterface;

class UserTableSeeder extends Seeder {

	/**
	 * @param UserInterface $user
	 */
	public function __construct (UserInterface $user)
	{
		$this->user = $user;
	}

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$users = $this->getUsers();

		foreach ($users as $user)
		{
			$first_name = $user['first_name'];
			$last_name = $user['last_name'];
			unset($user['first_name']);
			unset($user['last_name']);

			$sentryUser = Sentry::register($user, true);
			$sentryUser->profile->first_name = $first_name;
			$sentryUser->profile->last_name = $last_name;
			$sentryUser->profile->save();
			/*
			/$user = $this->user->find($sentryUser->id);
			$user->first_name = $user['first_name'];
			$user->last_name = $user['last_name'];
			$user->profile()->save($user);
			*/
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
			),
			array(
				'email'    => 'biospex@gmail.com',
				'password' => 'biospex',
				'first_name'    => 'Robert',
				'last_name'     => 'Bruhn',
			),
		);
	}
}