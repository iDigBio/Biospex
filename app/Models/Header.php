<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Header extends Model
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
    protected $table = 'headers';

    /**
     * @inheritDoc
     */
    protected $dates = ['deleted_at'];

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'project_id',
        'header'
    ];

    /**
     * Project relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Accessor for header column.
     * 
     * @param $value
     * @return mixed
     */
    public function getHeaderAttribute($value)
    {
        return unserialize($value);
    }

    /**
     * Mutator for header column.
     * 
     * @param $value
     */
    public function setHeaderAttribute($value)
    {
        $this->attributes['header'] = serialize($value);
    }
}
