<?php
/**
 * Download.php
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

class Download extends Eloquent {
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'downloads';

    protected $fillable = array(
        'expedition_id',
		'workflow_id',
        'file',
        'count'
    );

    /**
     * Array used by FactoryMuff to create Test objects
     */
    public static $factory = array(
        'expedition_id' => 'integer',
		'workflow_id' => 'integer',
        'file' => 'string',
        'count' => 'integer'
    );

	/**
	 * Boot function to add model events
	 */
	public static function boot ()
	{
		parent::boot();

		static::saved(function ()
		{
			Event::fire('download.saved');
		});

		static::deleting(function ()
		{
			Event::fire('download.deleting');
		});
	}

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo('Expedition');
    }

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function workflow ()
	{
		return $this->belongsTo('Workflow');
	}

	/**
	 * Get expired downloads
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public function getExpired()
	{
		return $this->where('count', '>', 5)->orWhere('created_at', '<', DB::raw('NOW() - INTERVAL 7 DAY'))->get();
	}
}