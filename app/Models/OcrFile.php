<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class OcrFile extends Model
{
    /**
     * @inheritDoc
     */
    protected $connection = 'mongodb';

    /**
     * Set Collection
     */
    protected $collection = 'ocr_files';

    /**
     * @inheritDoc
     */
    protected $primaryKey = '_id';

    /**
     * @inheritDoc
     */
    protected $guarded = ['_id'];

    /**
     * OrderBy
     *
     * @var array
     */
    protected $orderBy = [[]];


    protected static function boot()
    {
        parent::boot();
    }
}
