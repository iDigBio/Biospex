<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Actor extends BaseEloquentModel
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
     * @inheritDoc
     */
    protected $dates = ['deleted_at'];

    /**
     * Boot method for model.
     */
    protected static function boot()
    {
        parent::boot();

        self::deleting(function ($actor)
        {
            $actor->workflows()->delete();
        });

        self::restored(function ($actor)
        {
            $actor->workflows()->restore();
        });
    }

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
