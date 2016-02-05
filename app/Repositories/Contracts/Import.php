<?php namespace Biospex\Repositories\Contracts;

interface Import extends Repository
{
    public function findByError($error = 0);
}
