<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends BaseEloquentModel
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
    protected $table = 'notices';

    protected $fillable = [
        'message',
        'enabled'
    ];
}