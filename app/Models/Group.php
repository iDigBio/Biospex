<?php

namespace App\Models;

use App\Presenters\GroupPresenter;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UuidTrait;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use McCool\LaravelAutoPresenter\HasPresenter;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Group extends Model implements HasPresenter
{
    use UuidTrait, SoftCascadeTrait, SoftDeletes, LadaCacheTrait;

    /**
     * Enable soft delete.
     *
     * @var boolean
     */
    protected $softDelete = true;

    /**
     * Soft delete cascades.
     *
     * @var array
     */
    protected $softCascade = ['projects'];

    /**
     * @inheritDoc
     */
    protected $dates = ['deleted_at'];

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
     * Boot functions.
     */
    public static function boot()
    {
        parent::boot();

        static::bootUuidTrait();
    }

    /**
     * Get Resource Presenter.
     *
     * @return string
     */
    public function getPresenterClass()
    {
        return GroupPresenter::class;
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
        return $this->belongsToMany(User::class);
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
     * Trashed projects relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trashedProjects()
    {
        return $this->hasMany(Project::class)->orderBy('title')->onlyTrashed();
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