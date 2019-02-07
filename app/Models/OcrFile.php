<?php

namespace App\Models;

class OcrFile extends BaseMongoModel
{
    /**
     * Set Collection
     */
    protected $collection = 'ocr_files';

    /**
     * OrderBy
     *
     * @var array
     */
    protected $orderBy = [[]];
}