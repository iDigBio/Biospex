<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Header extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'headers';

    /**
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * @var array
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
