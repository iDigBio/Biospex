<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'workflows';

    /**
     * @var array
     */
    protected $fillable = ['title', 'enabled'];

    /**
     * Actor relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function actors()
    {
        return $this->belongsToMany(Actor::class)->withPivot('order')->orderBy('order');
    }

    /**
     * Project relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->hasMany(Project::class);
    }
}