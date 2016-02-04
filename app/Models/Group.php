<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

class Group extends Model
{
    use SoftDeletes;

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
        'user_id',
        'name',
        'permissions',
    ];

    /**
     * Returns owner of the group
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Return projects owned by the group
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projects()
    {
        return $this->hasMany(Project::class)->orderBy('title');
    }

    public function invites()
    {
        return $this->hasMany(Invite::class);
    }

    public function findAllGroupsWithProjects($allGroups)
    {
        foreach ($allGroups as $group) {
            $ids[] = $group->id;
        }

        return $groups = $this->has(Project::class)->whereIn('id', $ids)->orderBy('name')->get();
    }
}