<?php 

namespace App\Models;

use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Workflow extends Model
{
    use LadaCacheTrait, SoftCascadeTrait;

    /**
     * @inheritDoc
     */
    protected $table = 'workflows';

    /**
     * @inheritDoc
     */
    protected $fillable = ['title', 'enabled'];

    /**
     * Soft delete cascades.
     *
     * @var array
     */
    protected $softCascade = ['actors'];

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