<?php namespace Biospex\Repositories\Contracts;

interface OcrCsv extends Repository
{
    public function createOrFirst($attributes);
}

