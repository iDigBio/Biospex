<?php  namespace Biospex\Services\Queue;
/**
 * QueueAbstract.php
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

abstract class QueueAbstract {

    /**
     * Queue job.
     * @var object
     */
    protected $job;

    /**
     * Array containing data for queue job.
     * @var array
     */
    protected $data;

    /**
     * Required fire method.
     * @param $job
     * @param $data
     */
    abstract protected function fire($job, $data);

    /**
     * Delete a job from the queue
     */
    protected function delete()
    {
        $this->job->delete();
    }

    /**
     * Release a job back to the queue
     * @param null $seconds
     */
    protected function release($seconds = null)
    {
        $this->job->release($seconds);
    }

    /**
     * Return number of attempts on the job
     *
     * @return mixed
     */
    protected function getAttempts()
    {
        return $this->job->attempts();
    }

    /**
     * Get id of job
     *
     * @return mixed
     */
    protected function getJobId()
    {
        return $this->job->getJobId();
    }

}