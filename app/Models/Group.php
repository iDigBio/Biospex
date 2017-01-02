<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\UuidTrait;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;

class Group extends Model
{
    use SoftDeletes, UuidTrait, SoftCascadeTrait;

    /**
     * Soft delete cascades.
     *
     * @var array
     */
    protected $softCascade = ['projects'];

    /**
     * Protected dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'groups';

    /**
     * Allow soft deletes
     */
    protected $softDelete = true;

    /**
     * @var array
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'permissions',
    ];

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
     * Permissions relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
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