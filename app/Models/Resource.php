<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spiritix\LadaCache\Database\LadaCacheTrait;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;

class Resource extends Model implements AttachableInterface
{
    use SoftDeletes, LadaCacheTrait, PaperclipTrait;

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
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('document');

        parent::__construct($attributes);
    }
}
