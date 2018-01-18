<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Codesleeve\Stapler\ORM\EloquentTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class ProjectResource extends Model implements StaplerableInterface
{
    use EloquentTrait, LadaCacheTrait;

    /**
     * @inheritDoc
     */
    protected $table = 'project_resources';

    /**
     * @inheritDoc
     */
    protected $connection = 'mysql';

    /**
     * @inheritDoc
     */
    protected $primaryKey = 'id';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'project_id',
        'type',
        'name',
        'description',
        'upload'
    ];

    /**
     * Project constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('upload', ['styles' => []]);

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
