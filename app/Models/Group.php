<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\UuidTrait;
use Illuminate\Support\Facades\Event;

class Group extends Model
{
    use SoftDeletes, UuidTrait;

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
        'name',
        'permissions',
    ];

    /**
     * Handle model events.
     */
    public static function boot() {

        parent::boot();

        static::deleting(function ($model) {
            $model->title = $model->title . ':' . str_random();
            $model->save();
            $model->projects()->delete();
        });

        self::restored(function ($model)
        {
            $title = explode(':', $model->title);
            $model->title = $title[0];
            $model->save();
            $model->projects()->restore();
        });
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
     * Invites relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invites()
    {
        return $this->hasMany(Invite::class);
    }
}