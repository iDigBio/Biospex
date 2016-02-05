<?php  namespace Biospex\Repositories\Contracts;

interface Transcription extends Repository
{
    public function getCountByExpeditionId($expeditionId);
}
