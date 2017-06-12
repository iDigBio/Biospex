<?php

namespace App\Services\Queue;

abstract class QueueAbstract
{
    /**
     * Queue job.
     *
     * @var object
     */
    protected $job;

    /**
     * Array containing data for queue job.
     *
     * @var array
     */
    protected $data;

    /**
     * Required fire method.
     *
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
     *
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
