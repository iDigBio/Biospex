<?php namespace App\Repositories\Contracts;

interface Expedition extends Repository
{
    public function findByUuid($uuid);

    public function getAllExpeditions($id);
}
