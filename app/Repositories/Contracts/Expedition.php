<?php namespace App\Repositories\Contracts;

interface Expedition extends Repository
{
    public function getAllExpeditions($id);
}
