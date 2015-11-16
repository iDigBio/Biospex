<?php

class Actor extends Eloquent
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
    ];

    public function workflows()
    {
        return $this->belongsToMany('Workflow')->withPivot('order')->orderBy('order');
    }

    public function expeditions()
    {
        return $this->belongsToMany('Expedition', 'expedition_actor')
            ->withPivot('id', 'expedition_id', 'actor_id', 'state', 'error', 'queued', 'completed')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function downloads()
    {
        return $this->hasMany('Download');
    }
}
