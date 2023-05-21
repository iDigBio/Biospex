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

    protected $table = 'actor_workflow';
    public $primaryKey = 'id';
    public $incrementing = true;
    protected $guarded = [];
    public $timestamps = false;

    /**
     * @var array
     */
    public array $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildSortQuery()
    {
        // As we're sorting Artists belonging to a Track, we're setting this to filter using track_id
        return static::query()
            ->where('workflow_id', $this->workflow_id);
    }
}
