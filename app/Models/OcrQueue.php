<?php namespace Biospex\Models;
/**
 * OcrQueue.php
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

use Illuminate\Database\Eloquent\Model;
use Biospex\Models\Traits\UuidTrait;
use Biospex\Models\Traits\BelongsToProjectTrait;

class OcrQueue extends Model {

	use UuidTrait;
    use BelongsToProjectTrait;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'ocr_queue';

	/**
     * fillable  columns.
	 * @var array
	 */
	protected $fillable = [
		'project_id',
		'uuid',
		'data',
		'subject_count',
		'tries',
		'status',
		'error',
		'attachments'
	];

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

}
