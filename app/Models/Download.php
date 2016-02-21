<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UuidTrait;

class Download extends Model
{
    use UuidTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'downloads';

    protected $fillable = [
        'uuid',
        'expedition_id',
        'actor_id',
        'file',
        'data',
        'count'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function actor()
    {
        return $this->belongsTo(Actor::class);
    }

    /**
     * Get expired downloads
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getExpired()
    {
        return $this->where('count', '>', 5)->orWhere('created_at', '<', DB::raw('NOW() - INTERVAL 7 DAY'))->get();
    }
}
