<?php

namespace App\Services\Actor\NotesFromNatureManifest;

class NotesFromNatureManifest
{
    /**
     * @var
     */
    protected $fromNatureExport;

    public $state = [
        'export'
    ];

    public function __construct(NotesFromNatureManifestExport $export)
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