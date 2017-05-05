<?php 

namespace App\Models;



use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class Occurrence extends BaseMongoModel
{
    use SoftDeletes;

    /**
     * Enable soft delete.
     *
     * @var boolean
     */
    protected $softDelete = true;

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
}
