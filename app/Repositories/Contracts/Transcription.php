<?php  namespace App\Repositories\Contracts;

interface Transcription extends Repository
{
    public function getCountByExpeditionId($expeditionId);
}
