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

use Cartalyst\Sentry\Facades\Laravel\Sentry;
use Biospex\Repositories\Contracts\UserInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

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
        Model::unguard();

		$users = $this->getUsers();

		foreach ($users as $user)
		{
            $this->user->create($user);
		}
    }

	private function getUsers()
	{
		return [
			[
				'email'         => 'admin@biospex.org',
				'password'      => 'biospex',
				'first_name'    => 'Biospex',
				'last_name'     => 'Admin',
			],
			[
				'email'    => 'biospex@gmail.com',
				'password' => 'biospex',
				'first_name'    => 'Robert',
				'last_name'     => 'Bruhn',
			],

		];
	}
}