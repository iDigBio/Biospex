<?php
/**
 * TruncateTables.php
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

use Biospex\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Seeder;

class TruncateTables extends Seeder {
	public function run()
	{
		Helper::deleteDirectoryContents(Config::get('config.dataDir'));

		Model::unguard();

		DB::statement('SET FOREIGN_KEY_CHECKS=0;');

		$tableNames = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
		foreach ($tableNames as $name)
		{
			//if you don't want to truncate migrations
			if ($name == 'migrations')
			{
				continue;
			}
			DB::table($name)->truncate();
		}
		DB::connection('mongodb')->collection('subjects')->truncate();
	}
}