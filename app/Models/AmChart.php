<?php

namespace App\Models;

class AmChart extends BaseEloquentModel
{
    /**
     * @inheritDoc
     */
    protected $table = 'amcharts';

    /**
     * @inheritDoc
     */
    protected $fillable = ['project_id', 'series', 'data'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Mutator for data column.
     *
     * @param $value
     */
    public function setDataAttribute($value)
    {
        $this->attributes['data'] = json_encode($value);
    }

    /**
     * Accessor for data column.
     *
     * @param $value
     * @return false|string
     */
    public function getDataAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * Mutator for data column.
     *
     * @param $value
     */
    public function setSeriesAttribute($value)
    {
        $this->attributes['series'] = json_encode($value);
    }

    /**
     * Accessor for data column.
     *
     * @param $value
     * @return false|string
     */
    public function getSeriesAttribute($value)
    {
        return json_decode($value, true);
    }
}
