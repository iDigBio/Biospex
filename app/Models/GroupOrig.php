<?php namespace Biospex\Models;

/**
 * Group.php
 *
 * @package    Biospex Package
 * @version    2.0
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

use Cartalyst\Sentry\Groups\Eloquent\Group as SentryGroup;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\SoftDeletes;
use Biospex\Models\Traits\HasManyProjectsTrait;

class GroupOrig extends SentryGroup
{
    use SoftDeletes;
    use HasManyProjectsTrait;

    /**
     * Protect date fields.
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'groups';

    /**
     * Allow soft deletes
     */
    protected $softDelete = true;

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'permissions',
    ];

    /**
     * Returns owner of the group
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo('Biospex\Models\User', 'user_id');
    }


    public function findAllGroupsWithProjects($allGroups)
    {
        foreach ($allGroups as $group) {
            $ids[] = $group->id;
        }

        return $groups = $this->has('Projects')->whereIn('id', $ids)->orderBy('name')->get();
    }
}
