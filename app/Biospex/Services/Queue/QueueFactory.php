<?php namespace Biospex\Services\Queue;

/**
 * QueueFactory.php
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

use Illuminate\Support\Facades\App;
use Exception;

class QueueFactory
{
    /**
     * Create queue class to run.
     *
     * @param $job
     * @param $data
     * @throws Exception
     */
    public function fire($job, $data)
    {
        $class = $data['class'];
        $nameSpace = 'Biospex\Services\Queue\\';
        if (class_exists($nameSpace . $class)) {
            $obj = App::make($nameSpace . $class);
            $obj->fire($job, $data);

            return;
        } else {
            throw new Exception("Invalid queue class given.");
        }
    }
}
