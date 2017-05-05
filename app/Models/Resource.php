<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class Resource extends BaseEloquentModel
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
    protected $table = 'resources';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'title',
        'description',
        'document',
        'order'
    ];

    /**
     * @inheritDoc
     */
    protected $dates = ['deleted_at'];
}
