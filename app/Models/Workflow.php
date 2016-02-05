<?php namespace Biospex\Models;

use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'workflows';

    protected $fillable = ['workflow'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function actors()
    {
        return $this->belongsToMany(Actor::class)->withPivot('order')->orderBy('order');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->hasMany(Project::class);
    }

    public function selectList($value, $id)
    {
        return $this->lists($value, $id)->toArray();
    }
}