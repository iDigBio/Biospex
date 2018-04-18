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
        'download'
    ];

    /**
     * Project constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('download', ['styles' => []]);

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

    public function getDirty()
    {
        $dirty = parent::getDirty();

        return array_filter($dirty, function ($var) {
            return !($var instanceof \Codesleeve\Stapler\Attachment);
        });
    }

    /**
     * Set download file name to remove unwanted characters.
     *
     * @param $value
     */
    public function setDownloadFileNameAttribute($value)
    {
        $this->attributes['download_file_name'] = preg_replace("/[^\w\-\.]/", '', $value);
    }
}
