<?php

namespace App\Services\Actor\NfnLegacy;

class NfnLegacy
{
    /**
     * @var
     */
    protected $fromNatureExport;

    public $state = [
        'export'
    ];

    public function __construct(NfnLegacyExport $export)
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