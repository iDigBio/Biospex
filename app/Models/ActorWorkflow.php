<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Models;

use IDigAcademy\AutoCache\Traits\Cacheable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * Class ActorWorkflow
 *
 * Represents the pivot model for the many-to-many relationship between
 * Actor and Workflow models. This model extends the basic pivot functionality
 * with sortable capabilities, allowing actors to be ordered within workflows.
 *
 * Key Features:
 * - Supports caching for improved performance
 * - Implements sortable functionality for ordering actors in workflows
 * - Uses AsPivot trait for enhanced pivot model capabilities
 * - Provides custom sort query building for workflow-specific ordering
 */
class ActorWorkflow extends BaseEloquentModel implements Sortable
{
    use AsPivot, Cacheable, HasFactory, SortableTrait;

    /**
     * The name of the database table associated with the model.
     *
     * @var string
     */
    protected $table = 'actor_workflow';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    public $primaryKey = 'id';

    /**
     * Indicates if the model should use auto-incrementing primary keys.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that are not mass assignable.
     * Empty array means all attributes are mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Configuration array for the sortable functionality.
     * Defines the column name for ordering and behavior during creation.
     *
     * @var array<string, mixed>
     */
    public array $sortable = [
        'order_column_name' => 'order',    // Column used for sorting
        'sort_when_creating' => true,      // Automatically sort when creating new records
    ];

    /**
     * Build a query for sorting actors within a specific workflow.
     *
     * This method is required by the Sortable interface and defines the scope
     * for sorting operations. It ensures that actors are only sorted within
     * their respective workflow context, not globally across all workflows.
     *
     * @return \Illuminate\Database\Eloquent\Builder The query builder scoped to the current workflow
     */
    public function buildSortQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return static::query()->where('workflow_id', $this->workflow_id);
    }
}
