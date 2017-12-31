<?php

namespace App\Interfaces;

interface Download extends Eloquent
{

    /**
     * @return mixed
     */
    public function getDownloadsForCleaning();
}
