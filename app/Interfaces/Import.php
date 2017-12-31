<?php

namespace App\Interfaces;


interface Import extends Eloquent
{

    /**
     * @return mixed
     */
    public function getImportsWithoutError();
}