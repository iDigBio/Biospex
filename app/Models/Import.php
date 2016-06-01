<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'imports';

    protected $fillable = [
        'user_id',
        'project_id',
        'file',
        'error'
    ];

    /**
     * Project relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * User relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
