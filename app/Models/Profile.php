<?php

namespace App\Models;

use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use App\Models\Traits\Presentable;

class Profile extends BaseEloquentModel implements AttachableInterface
{
    use PaperclipTrait, Presentable;

    /**
     * @inheritDoc
     */
    protected $table = 'profiles';

    /**
     * @inheritDoc
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'timezone',
        'avatar',
    ];

    /**
     * Profile constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('avatar', [
            'variants' => [
                'medium' => '160x160', 'small' => '25x25'
            ],
            'url'  => config('config.missing_avatar_medium'),
            'urls' => [
                'small' => config('config.missing_avatar_small'),
                'medium' => config('config.missing_avatar_medium'),
            ],
        ]);

        parent::__construct($attributes);
    }

    /**
     * Boot function to add model events
     */
    public static function boot()
    {
        parent::boot();
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
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
