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
class Expedition extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'expeditions';

    /**
     * Allow soft deletes
     */
    protected $softDelete = true;

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
        'workflow_id'
    );

    /**
     * Array used by FactoryMuff to create Test objects
     */
    public static $factory = array(
        'project_id' => 'factory|Project',
        'title' => 'string',
        'description' => 'text',
        'keywords' => 'string',
        'workflow_id' => 'integer'
    );

    /**
     * Boot function to add model events
     */
    public static function boot(){
        parent::boot();

        Project::saving(function($model)
        {
            $model->target_fields = Input::all();
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

    /**
     * Return expeditions by project id
     *
     * @param $projectId
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findByProjectId($projectId)
    {
        return $this->where('project_id', $projectId)->get();
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

    public function getTotalSubjectsAttribute()
    {
        return $this->belongsToMany('Subject')->whereExpeditionId($this->id)->count();
    }
}
