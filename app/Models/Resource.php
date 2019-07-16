<?php

namespace App\Models;

use App\Models\Traits\Presentable;
use App\Presenters\ResourcePresenter;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;

class Resource extends BaseEloquentModel implements AttachableInterface
{
    use PaperclipTrait, Presentable;

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
