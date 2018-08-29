<?php

namespace App\Models;

use App\Presenters\ResourcePresenter;
use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use App\Models\Traits\Presentable;

class Resource extends Model implements AttachableInterface
{
    use LadaCacheTrait, PaperclipTrait, Presentable;

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
     * @var string
     */
    protected $presenter = ResourcePresenter::class;

    /**
     * Resource constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('document');

        parent::__construct($attributes);
    }

}
