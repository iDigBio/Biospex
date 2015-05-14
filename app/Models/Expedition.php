<?php namespace Biospex\Models;
/**
 * Expedition.php
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

use Jenssegers\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Biospex\Models\Traits\UuidTrait;
use Biospex\Models\Traits\BelongsToProjectTrait;
use Biospex\Models\Traits\BelongsToManySubjectsTrait;
use Biospex\Models\Traits\HasOneWorkflowManagerTrait;
use Biospex\Models\Traits\HasManyDownloadsTrait;
use Biospex\Models\Traits\BelongsToManyActorsTrait;

class Expedition extends Eloquent {

    use SoftDeletes;
	use UuidTrait;
    use BelongsToProjectTrait;
    use BelongsToManySubjectsTrait;
    use HasOneWorkflowManagerTrait;
    use HasManyDownloadsTrait;
    use BelongsToManyActorsTrait;

    protected $dates = ['deleted_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'expeditions';

	protected $connection = 'mysql';

	protected $primaryKey = 'id';

    /**
     * Accepted attributes
     *
     * @var array
     */
    protected $fillable = [
		'uuid',
        'project_id',
        'title',
        'description',
        'keywords',
    ];

    /**
     * Boot function to add model events
     */
    public static function boot(){
        parent::boot();
    }


	/**
	 * Find by uuid.
	 *
	 * @param $uuid
	 * @return mixed
	 */
	public function findByUuid($uuid)
	{
		return $this->where('uuid', pack('H*', str_replace('-', '', $uuid)))->get();
	}

    /**
     * Accessor for created_at
     *
     * @param $value
     * @return bool|string
     */
    public function getCreatedAtAttribute($value)
    {
        return date("m/d/Y", strtotime($value));
    }

    /**
     * Accessor updated_at
     *
     * @param $value
     * @return bool|string
     */
    public function getUpdatedAtAttribute($value)
    {
        return date("m/d/Y", strtotime($value));
    }

	/**
	 * Get counts attribute
	 *
	 * @return int
	 */
	public function getSubjectsCountAttribute ()
	{
		return $this->subjects()->count();
	}

	/**
	 * Return completed through relationship
	 * @return mixed
	 */
	public function actorsCompletedRelation ()
	{
		return $this->belongsToMany('Actor', 'expedition_actor')->selectRaw('expedition_id, avg(completed) as avg')->groupBy('expedition_id');
	}

	/**
	 * Get completed attribute of actors
	 *
	 * @return int
	 */
	public function getActorsCompletedAttribute ()
	{
		return $this->actorsCompletedRelation->first() ? $this->actorsCompletedRelation->first()->avg : 0;
	}
}
