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

    public function __construct(NfnPanoptesExport $export)
    {
        $this->export = $export;
    }

    public function process($actor)
    {
        switch($this->state[$actor->pivot->state]) {
            case 'export':
                $this->export->process($actor);
                break;
            default:
                break;
        }
    }
}