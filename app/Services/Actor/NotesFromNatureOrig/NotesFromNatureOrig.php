<?php

namespace App\Services\Actor\NotesFromNatureOrig;

class NotesFromNatureOrig
{
    /**
     * @var
     */
    protected $fromNatureExport;

    public $state = [
        'export'
    ];

    public function __construct(NotesFromNatureOrigExport $nfnExport)
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