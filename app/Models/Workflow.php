<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'workflows';

    /**
     * @var array
     */
    protected $fillable = ['workflow'];

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