<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGridField extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_grid_field';

    /**
     * @var array
     */
    protected $fillable = [
        'type',
        'user_id',
        'project_id',
        'expedition_id',
        'fields'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class);
    }

    public function findByUserProjectExpedition($userId, $projectId, $expeditionId)
    {
        return $this
            ->where('user_id', $userId)
            ->where('project_id', $projectId)
            ->where('expedition_id', $expeditionId)
            ->first();
    }
}
