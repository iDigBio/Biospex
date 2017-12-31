<?php

namespace App\Interfaces;

interface Notice extends Eloquent
{

    /**
     * @return mixed
     */
    public function getEnabledNotices();
}