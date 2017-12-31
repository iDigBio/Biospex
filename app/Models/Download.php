<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UuidTrait;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Download extends Model
{
    use UuidTrait, LadaCacheTrait;

    /**
     * @inheritDoc
     */
    protected $table = 'downloads';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'uuid',
        'expedition_id',
        'actor_id',
        'file',
        'type',
        'data',
        'count'
    ];

    /**
     * Expedition relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class);
    }

    /**
     * Actor relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function actor()
    {
        return $this->belongsTo(Actor::class);
    }
}
