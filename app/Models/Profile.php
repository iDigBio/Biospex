<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $dates = ['created_at', 'updated_at'];

    protected $fillable = [
        'first_name',
        'last_name',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'profiles';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
