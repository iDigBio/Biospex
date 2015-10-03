<?php namespace App\Models;

/**
 * Expedition.php
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

use Jenssegers\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\UuidTrait;
use App\Models\Traits\BelongsToProjectTrait;
use App\Models\Traits\HasOneWorkflowManagerTrait;
use App\Models\Traits\HasManyDownloadsTrait;
use App\Models\Traits\HasManyUserGridFieldTrait;

class Expedition extends Eloquent
{
    use SoftDeletes;
    use UuidTrait;
    use BelongsToProjectTrait;
    use HasOneWorkflowManagerTrait;
    use HasManyDownloadsTrait;
    use HasManyUserGridFieldTrait;

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
    public static function boot()
    {
        parent::boot();
    }

    /**
     * Belongs to many
     * $expedition->subjects()->attach($subject) adds expedition ids in subjects
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subjects()
    {
        return $this->belongsToMany('App\Models\Subject');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actors()
    {
        return $this->belongsToMany('App\Models\Actor')->withPivot('state', 'completed')->withTimestamps();
    }

    /**
     * Return completed through relationship
     * @return mixed
     */
    public function actorsCompletedRelation()
    {
        return $this->belongsToMany('App\Models\Actor')->selectRaw('expedition_id, avg(completed) as avg')->groupBy('expedition_id');
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
     * Get counts attribute
     *
     * @return int
     */
    public function getSubjectsCountAttribute()
    {
        return $this->subjects()->count();
    }

    /**
     * Get completed attribute of actors
     *
     * @return int
     */
    public function getActorsCompletedAttribute()
    {
        return $this->actorsCompletedRelation->first() ? $this->actorsCompletedRelation->first()->avg : 0;
    }
}
