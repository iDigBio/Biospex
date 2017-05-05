<?php 

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends BaseEloquentModel
{
    use SoftDeletes;

    /**
     * Enable soft delete.
     *
     * @var boolean
     */
    protected $softDelete = true;

    /**
     * @inheritDoc
     */
    protected $table = 'workflows';

    /**
     * @inheritDoc
     */
    protected $fillable = ['title', 'enabled'];

    /**
     * @inheritDoc
     */
    protected $dates = ['deleted_at'];

    /**
     * @return mixed
     */
    public function actors()
    {
        return $this->belongsToMany(Actor::class)->withPivot('order')->orderBy('order');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function project()
    {
        return $this->hasMany(Project::class);
    }
}