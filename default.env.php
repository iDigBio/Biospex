<?php
/**
 * default.env.php
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

// Descriptions can be found in /app/config files
return [
	'site.ip'          => '127.0.0.1',
	'site.url'         => 'http://yoursite.com',
	'site.debug'        => false,
	'site.key'         => 'KLMJHNjsnkwotikgnkMJkmloghweFGTG',
	'site.timezone'    => 'America/New_York',
	'site.locale'       => 'en',
	'site.adminEmail'  => 'youremail@provider.com',
	'site.registration' => true,

	'mysql.host'       => 'localhost',
	'mysql.database'   => 'db',
	'mysql.username'   => 'user',
	'mysql.password'   => 'pass',

	'mongodb.host'     => 'localhost',
	'mongodb.port'      => 27017,
	'mongodb.username' => 'user',
	'mongodb.password' => 'pass',
	'mongodb.database' => 'db',

	'mail.driver'      => 'smtp',
	'mail.host'        => 'smtp.mailgun.org',
	'mail.port'         => 587,
	'mail.address'     => 'youremail@provider.com',
	'mail.name'        => 'Your Team',
	'mail.encryption'   => 'tls',
	'mail.username'    => 'user',
	'mail.password'    => 'pass',
	'mail.sendmail'     => '/usr/sbin/sendmail -bs',
	'mail.pretend'      => false,
];