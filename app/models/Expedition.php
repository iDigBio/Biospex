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
			$model->subject()->detach();
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
     * Has many relationships
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subject()
    {
        return $this->belongsToMany('Subject');
    }

	public function subjectCountRelation ()
	{
		return $this->belongsToMany('Subject')->selectRaw('expedition_id, count(*) as count')->groupBy('expedition_id');
	}

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowManager()
    {
        return $this->hasMany('WorkflowManager');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function download()
    {
		return $this->hasMany('Download');
    }

    /**
     * Return expeditions by project id
     *
     * @param $projectId
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function byProjectId($projectId)
    {
        return $this->where('project_id', $projectId)->orderBy('title')->get();
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

	public function getSubjectCountAttribute ()
	{
		return $this->subjectCountRelation->first() ? $this->subjectCountRelation->first()->count : 0;
	}
}
