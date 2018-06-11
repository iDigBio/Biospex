<?php

namespace App\Models;

use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Actor extends Model
{
    use LadaCacheTrait, SoftCascadeTrait;

    /**
     * @inheritDoc
     */
    protected $table = 'actors';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'title',
        'url',
        'class',
        'private'
    ];

    /**
     * Soft delete cascades.
     *
     * @var array
     */
    protected $softCascade = ['workflows', 'downloads', 'contacts', 'exportQueues'];


    /**
     * Workflow relationship.
     *
     * @return mixed
     */
    public function workflows()
    {
        return $this->belongsToMany(Workflow::class)->withPivot('order');
    }

    /**
     * Download relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function downloads()
    {
        return $this->hasMany(Download::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {
        return $this->hasMany(ActorContact::class);
    }

    /**
     * Expedition relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function expeditions()
    {
        return $this->belongsToMany(Expedition::class, 'actor_expedition')
            ->withPivot('id', 'expedition_id', 'actor_id', 'state', 'total', 'processed', 'error', 'queued', 'completed', 'order')
            ->orderBy('order')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function exportQueues()
    {
        return $this->hasMany(ExportQueue::class);
    }
}
