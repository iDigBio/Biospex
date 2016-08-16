<?php

namespace App\Services\Actor;


use App\Services\Api\NfnApi;

class ActorApiService
{

    /**
     * @var NfnApi
     */
    public $nfnApi;

    /**
     * ActorApiService constructor.
     * @param NfnApi $nfnApi
     */
    public function __construct(NfnApi $nfnApi)
    {
        $this->nfnApi = $nfnApi;
    }
}