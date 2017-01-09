<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Codesleeve\Stapler\ORM\EloquentTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;

class Profile extends Model implements StaplerableInterface
{
    use EloquentTrait;

    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'timezone',
        'avatar'
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'profiles';

    /**
     * Profile constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('avatar', ['styles' => ['medium' => '160x160', 'small' => '25x25']]);

        parent::__construct($attributes);
    }

    /**
     * Boot function to add model events
     */
    public static function boot()
    {
        parent::boot();

        static::bootStapler();
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


    /**
     * Get full name.
     *
     * @return string
     */
    public function getFullNameAttribute() {
        return $this->first_name . ' ' . $this->last_name;
    }
}
