<?php
/**
 * Subject.php
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

class Subject extends Eloquent {

    use SoftDeletingTrait;
    protected $dates = ['deleted_at'];

    /**
     * Set connection since extending from Moloquent
     */
    protected $connection = 'mysql';

    /**
     * Set primary id of table
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = array(
        'mongo_id',
        'project_id',
        'object_id'
    );

    /**
     * Return count of project subjects not assigned to expeditions
     * @param $projectId
     * @return mixed
     */
    public function getUnassignedSubjectCount($projectId)
    {
        return Subject::has('expedition','<', 1)
            ->where('project_id', $projectId)
            ->count();
    }

    /**
     * Return project subjects not assigned to expeditions by limit
     * @param $input
     * @return mixed
     */
    public function getUnassignedSubjects($input)
    {
        $ids = $this->has('expedition','<',1)
            ->where('project_id',$input['project_id'])
            ->take($input['subjects'])
            ->get(array('id'))
            ->toArray();
        return array_flatten($ids);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function project()
    {
        return $this->belongsTo('Project');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function expedition()
    {
        return $this->belongsToMany('Expedition');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function subjectDoc()
    {
        return $this->hasOne('SubjectDoc', '_id', 'mongo_id');
    }
}