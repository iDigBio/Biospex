<?php

namespace App\Models;

use App\Presenters\ResourcePresenter;
use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use McCool\LaravelAutoPresenter\HasPresenter;

class Resource extends Model implements AttachableInterface, HasPresenter
{
    use LadaCacheTrait, PaperclipTrait;

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
     * Resource constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('document');

        parent::__construct($attributes);
    }

    /**
     * Get Resource Presenter.
     *
     * @return string
     */
    public function getPresenterClass()
    {
        return ResourcePresenter::class;
    }
}
