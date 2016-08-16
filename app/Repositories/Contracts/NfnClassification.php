<?php namespace App\Repositories\Contracts;

interface NfnClassification extends Repository
{
    /**
     * Return classification count grouped by finished_at date.
     *
     * @param $workflow
     * @return mixed
     */
    public function getExpeditionsGroupByFinishedAt($workflow);
}
