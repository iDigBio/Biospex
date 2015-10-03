<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HasManyProjectsTrait;

class Group extends Model
{
    use SoftDeletes;
    use HasManyProjectsTrait;

    /**
     * Protect date fields.
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * A Group may be given various permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Grant the given permission to a group.
     *
     * @param  Permission $permission
     * @return mixed
     */
    public function givePermissionTo(Permission $permission)
    {
        return $this->permissions()->save($permission);
    }

    public function findAllGroupsWithProjects($allGroups)
    {
        foreach ($allGroups as $group) {
            $ids[] = $group->id;
        }

        return $groups = $this->has(Project::class)->whereIn('id', $ids)->orderBy('name')->get();
    }
}