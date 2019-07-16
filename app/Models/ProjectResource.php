<?php

namespace App\Models;

use App\Models\Traits\Presentable;
use App\Presenters\ProjectResourcePresenter;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;

class ProjectResource extends BaseEloquentModel implements AttachableInterface
{
    use PaperclipTrait, Presentable;

    /**
     * @inheritDoc
     */
    protected $table = 'project_resources';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'project_id',
        'type',
        'name',
        'description',
        'download'
    ];

    /**
     * @var string
     */
    protected $presenter = ProjectResourcePresenter::class;

    /**
     * Project constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('download');

        parent::__construct($attributes);
    }

    /**
     * Project relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
