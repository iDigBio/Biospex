<?php
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
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Expedition extends Eloquent {

    use SoftDeletingTrait;
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
    protected $fillable = array(
        'project_id',
        'title',
        'description',
        'keywords',
    );

    /**
     * Boot function to add model events
     */
    public static function boot(){
        parent::boot();

		// Delete associated subjects from expedition_subjects
		static::deleting(function($model) {
			$model->subjects()->detach();
		});
    }

    /**
     * Belongs to relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo('Project');
    }

    /**
     * Belongs to many
	 * $expedition->subjects()->attach($subject) adds expedition ids in subjects
	 *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
	public function subjects ()
    {
        return $this->belongsToMany('Subject')->withTimestamps();
    }

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function workflowManager ()
    {
        return $this->hasOne('WorkflowManager');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
	public function downloads ()
    {
		return $this->hasMany('Download');
    }

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function actors()
	{
		return $this->belongsToMany('Actor', 'expedition_actor')->withPivot('state', 'completed')->withTimestamps();
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
	 * Return count through relationship
	 * @return mixed
	 */
	public function subjectsCountRelation ()
	{
		return $this->belongsToMany('Subject')->selectRaw('expedition_id, count(*) as count')->groupBy('expedition_id');
	}

	/**
	 * Get counts attribute
	 *
	 * @return int
	 */
	public function getSubjectsCountAttribute ()
	{
		return $this->subjectsCountRelation->first() ? $this->subjectsCountRelation->first()->count : 0;
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
