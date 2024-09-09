<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * Class ActorWorkflow
 * Pivot table.
 */
class ActorWorkflow extends BaseEloquentModel implements Sortable
{
    use AsPivot, SortableTrait;

    /**
     * @var string
     */
    protected $table = 'actor_workflow';

    /**
     * @var string
     */
    public $primaryKey = 'id';

    /**
     * @var bool
     */
    public $incrementing = true;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var bool
     */
    public $timestamps = false;

    public array $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildSortQuery()
    {
        return static::query()->where('workflow_id', $this->workflow_id);
    }
}
