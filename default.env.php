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

return [
	'site.ip'           => '',
	'site.url'          => '',
	'site.debug'        => false,
	'site.key'          => '',
	'site.timezone'     => '',
	'site.locale'       => 'en',
	'site.adminEmail'   => '',
	'site.registration' => true,

	'mysql.host'        => '',
	'mysql.database'    => '',
	'mysql.username'    => '',
	'mysql.password'    => '',

	'mongodb.host'      => '',
	'mongodb.port'      => 27017,
	'mongodb.username'  => '',
	'mongodb.password'  => '',
	'mongodb.database'  => '',

	'mail.driver'       => '',
	'mail.host'         => '',
	'mail.port'         => 587,
	'mail.address'      => '',
	'mail.name'         => '',
	'mail.encryption'   => 'tls',
	'mail.username'     => '',
	'mail.password'     => '',
	'mail.sendmail'     => '/usr/sbin/sendmail -bs',
	'mail.pretend'      => false,
];