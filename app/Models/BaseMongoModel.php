<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class BaseMongoModel extends Model
{
    /**
     * @inheritDoc
     */
    protected $connection = 'mongodb';

    /**
     * @inheritDoc
     */
    protected $primaryKey = '_id';

    /**
     * @inheritDoc
     */
    protected $guarded = ['_id'];

    /**
     * @inheritDoc
     */
    public $incrementing = false;

}