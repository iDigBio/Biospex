<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Notice extends Model
{
    use SoftDeletes, LadaCacheTrait;

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