<?php namespace Biospex\Models;

use Illuminate\Database\Eloquent\Model;

class Actor extends Model
{

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

    public function workflows()
    {
        return $this->belongsToMany(Workflow::class)->withPivot('order')->orderBy('order');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function downloads()
    {
        return $this->hasMany(Download::class);
    }

    public function expeditions()
    {
        return $this->belongsToMany(Expedition::class, 'actor_expedition')
            ->withPivot('id', 'expedition_id', 'actor_id', 'state', 'error', 'queued', 'completed')
            ->withTimestamps();
    }
}
