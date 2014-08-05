<?php
/**
 * Invite.php
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

class Invite extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'invites';

    protected $fillable = array(
        'group_id',
        'email',
        'code'
    );

    /**
     * Array used by FactoryMuff to create Test objects
     */
    public static $factory = array(
        'group_id' => 'factory|Group',
        'email' => 'string',
        'code' => 'string'
    );

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo('Group');
    }

    /**
     * Group id scope
     *
     * @param $query
     * @param $id
     * @return mixed
     */
    public function scopeGroup($query, $id)
    {
        return $query->whereGroupId($id);
    }

    /**
     * Code scope
     *
     * @param $query
     * @param $code
     * @return mixed
     */
    public function scopeCode($query, $code)
    {
        return $query->whereCode($code);
    }

    /**
     * Email scope
     *
     * @param $query
     * @param $email
     * @return mixed
     */
    public function scopeEmail($query, $email)
    {
        return $query->whereCode($email);
    }

    /**
     * Find invite by code
     *
     * @param $code
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findByCode($code)
    {
        return $this->code($code)->first();
    }

    /**
     * Find duplicate
     *
     * @param $id
     * @param $email
     * @return mixed
     */
    public function checkDuplicate($id, $email)
    {
        return $this->group($id)->email($email)->first();
    }
}