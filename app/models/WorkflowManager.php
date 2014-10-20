<?php
/**
 * WorkflowExec.php
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
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class WorkflowManager extends Eloquent {

    use SoftDeletingTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'workflow_manager';

    protected $fillable = array(
        'workflow_id',
        'expedition_id',
        'user_id'
    );

	/**
	 * Scope not deleted
	 *
	 * @param $query
	 * @return mixed
	 */
	public function scopeNotDeleted($query)
	{
    	return $query->whereNotNull('deleted_at');
	}

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function expedition()
    {
        return $this->belongsTo('Expedition');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function user()
    {
        return $this->belongsTo('User');
    }

    public function scopeExpeditionId($query, $id)
    {
        return $query->where('expedition_id', '=', $id);
    }

    /**
     * Get workflow process by expedition id
     *
     * @param $id
     * @return mixed
     */
    public function getByExpeditionId($id, $deleted)
    {
        return !$deleted ? $this->expeditionid($id)->first() : $this->expeditionid($id)->NotDeleted()->first();
    }

}