<?php

namespace App\Interfaces;

interface State extends Eloquent
{
    /**
     * @return mixed
     */
    public function truncateTable();
}