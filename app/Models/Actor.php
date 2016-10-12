<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Actor extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'actors';

    protected $fillable = [
        'title',
        'url',
        'class',
        'private'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
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
}
