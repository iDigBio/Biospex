<?php  namespace App\Repositories\Contracts;

interface Transcription extends Repository
{
    public function getCountByExpeditionId($expeditionId);
    public function getEarliestDate($project_id, $expedition_id);
}
