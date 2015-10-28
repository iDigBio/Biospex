<?php

namespace Biospex\Services\Actor\NotesFromNature;

class NotesFromNature
{
    /**
     * @var
     */
    protected $fromNatureExport;

    public $state = [
        'export'
    ];

    public function __construct(NotesFromNatureExport $nfnExport)
    {
        $this->nfnExport = $nfnExport;
    }

    public function process($actor)
    {
        switch($this->state[$actor->state]) {
            case 'export':
                $this->nfnExport->process($actor);
                break;
            default:
                break;
        }

        return;
    }
}