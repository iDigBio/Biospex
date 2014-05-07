<?php
/**
 * User.php
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
use Cartalyst\Sentry\Users\Eloquent\User as SentryUser;

class User extends SentryUser {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Allow soft deletes
     */
    protected $softDelete = true;

    /**
     * Array used by FactoryMuff to create Test objects
     */
    public static $factory = array(
        'email' => 'email',
        'password' => 'string',
        'permissions' => array(),
        'activated' => 1,
        'first_name' => 'string',
        'last_name' => 'string',
    );

    /**
     * Return groups owned by the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ownsGroups()
    {
        return $this->hasMany('Group');
    }

    /**
     * Used during phpunit tests for setting hash
     */
    public static function boot()
    {
        self::$hasher = new Cartalyst\Sentry\Hashing\NativeHasher;
    }

}
