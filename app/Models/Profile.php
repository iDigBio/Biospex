<?php

namespace App\Models;

use App\Presenters\ProfilePresenter;
use Illuminate\Database\Eloquent\Model;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use McCool\LaravelAutoPresenter\HasPresenter;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Profile extends Model implements AttachableInterface, HasPresenter
{
    use PaperclipTrait, LadaCacheTrait;

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
     * @inheritDoc
     */
    protected $table = 'profiles';

    /**
     * Profile constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('avatar', ['variants' => ['medium' => '160x160', 'small' => '25x25']]);

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
     * Get Resource Presenter.
     *
     * @return string
     */
    public function getPresenterClass()
    {
        return ProfilePresenter::class;
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
