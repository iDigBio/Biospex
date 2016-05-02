<?php

namespace App\Services\Actor\NfnPanoptes;

class NfnPanoptes
{
    /**
     * @var
     */
    protected $fromNatureExport;

    public $state = [
        'export'
    ];

    public function __construct(NfnPanoptesExport $nfnExport)
    {
        $this->nfnExport = $nfnExport;
    }

    public function process($actor)
    {
        switch($this->state[$actor->pivot->state]) {
            case 'export':
                $this->nfnExport->process($actor);
                break;
            default:
                break;
        }
    }
}