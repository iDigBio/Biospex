<?php namespace Biospex\Repositories\Contracts;

interface OcrQueue extends Repository
{
    public function findByProjectId($id);

    public function getSubjectCountSum($id);
}
