<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToUserTrait;

class Profile extends Model
{
    use BelongsToUserTrait;

    /**
     * Protect date columns.
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Fillable columns.
     * @var array
     */
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
}
