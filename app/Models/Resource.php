<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Resource extends Model
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

    /**
     * Set document to remove unwanted characters.
     *
     * @param $value
     */
    public function setDocumentFileNameAttribute($value)
    {
        $this->attributes['document'] = preg_replace("/[^\w\-\.]/", '', $value);
    }
}
