<?php

namespace App\Services\Actor\NotesFromNature2;

class NotesFromNature2
{
    /**
     * @var
     */
    protected $fromNatureExport;

    public $state = [
        'export'
    ];

    public function __construct(NotesFromNature2Export $nfnExport)
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

        return;
    }
}