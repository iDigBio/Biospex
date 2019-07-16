<?php

namespace App\Models;

use App\Models\Traits\Presentable;
use App\Models\Traits\UuidTrait;
use App\Presenters\GroupPresenter;

class Group extends BaseEloquentModel
{
    use UuidTrait, Presentable;

    /**
     * @inheritDoc
     */
    protected $table = 'groups';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'title'
    ];

    /**
     * @var string
     */
    protected $presenter = GroupPresenter::class;

    /**
     * Boot functions.
     */
    public static function boot()
    {
        parent::boot();

        static::bootUuidTrait();
    }

    /**
     * User as owner relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Users relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('group_id');
    }

    /**
     * Projects relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projects()
    {
        return $this->hasMany(Project::class)->orderBy('title');
    }

    /**
     * Expeditions relationship
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function expeditions()
    {
        return $this->hasManyThrough(Expedition::class, Project::class);
    }

    /**
     * Invites relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invites()
    {
        return $this->hasMany(Invite::class);
    }
}