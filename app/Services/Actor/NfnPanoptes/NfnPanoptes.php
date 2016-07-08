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
        if ($this->state[$actor->pivot->state] === 'export') {
            $this->export->process($actor);
        }
    }
}