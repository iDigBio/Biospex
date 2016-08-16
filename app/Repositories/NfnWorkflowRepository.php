<?php
namespace App\Repositories;

use App\Repositories\Contracts\NfnWorkflow;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class NfnWorkflowRepository extends Repository implements NfnWorkflow, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\NfnWorkflow::class;
    }
}