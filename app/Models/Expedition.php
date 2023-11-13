<?php
/*
 * Copyright (C) 2015  Biospex
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
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Models;

use App\Models\Traits\UuidTrait;
use App\Presenters\ExpeditionPresenter;
use App\Models\Traits\Presentable;
use MongoDB\Laravel\Eloquent\HybridRelations;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;

/**
 * Class Expedition
 *
 * @package App\Models
 */
class Expedition extends BaseEloquentModel implements AttachableInterface
{
    use UuidTrait, HybridRelations, Presentable, PaperclipTrait;

    /**
     * @inheritDoc
     */
    protected $table = 'expeditions';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'uuid',
        'project_id',
        'title',
        'description',
        'keywords',
        'logo',
        'workflow_id',
        'geo_locate_form_id',
        'completed',
        'locked'
    ];

    /**
     * @var string
     */
    protected $presenter = ExpeditionPresenter::class;

    /**
     * Model Boot
     */
    public static function boot()
    {
        parent::boot();
    }

    /**
     * Expedition constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('logo', [
            'variants' => [
                'medium' => [
                    'resize'      => ['dimensions' => '318x208'],
                ]
            ],
            'url'  => config('config.missing_expedition_logo'),
            'urls' => [
                // This fallback URL is only given for the 'thumb' variant.
                'medium' => config('config.missing_expedition_logo'),
            ],
        ]);
        //$this->hasAttachedFile('logo', ['resize' => ['dimensions' => '318x208']]);

        parent::__construct($attributes);
    }

    /**
     * Project relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * ExpeditionStat relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function stat(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ExpeditionStat::class);
    }

    /**
     * Return expedition stat transcriptions have started.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function statWithTranscriptions(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ExpeditionStat::class)->where('transcriptions_completed', '>', 0);
    }

    /**
     * Subject relationship.
     *  $expedition->subjects()->attach($subject) adds expedition ids in subjects
     *
     * @return \MongoDB\Laravel\Relations\BelongsToMany
     */
    public function subjects(): \MongoDB\Laravel\Relations\BelongsToMany
    {
        return $this->belongsToMany(Subject::class);
    }

    /**
     * Workflow relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflow(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * WorkflowManager relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function workflowManager(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(WorkflowManager::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function downloads(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Download::class);
    }

    /**
     * Download Zooniverse Export relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function zooniverseExport(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Download::class)->where('actor_id', config('zooniverse.actor_id'))->where('type', 'export');
    }

    /**
     * Download GeoLocate Export relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function geoLocateExport()
    {
        return $this->hasOne(Download::class)->where('actor_id', config('geolocate.actor_id'))->where('type', 'export');
    }

    /**
     * Ocr Queue relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ocrQueue(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OcrQueue::class);
    }

    /**
     * Actors relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function actors(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Actor::class, 'actor_expedition')
            ->withPivot('id', 'expedition_id', 'actor_id', 'state', 'total', 'error', 'order', 'expert')
            ->orderBy('order')
            ->withTimestamps();
    }

    /**
     * Return zooniverse actor relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function zooniverseActor(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        $pivot = [
            'id',
            'expedition_id',
            'actor_id',
            'state',
            'total',
            'error',
            'order',
            'expert'
        ];

        return $this->belongsToMany(Actor::class, 'actor_expedition')
            ->withPivot($pivot)
            ->wherePivot('actor_id', config('zooniverse.actor_id'));
    }

    /**
     * Return zooniverseActor attribute.
     * $expedition->zooniverseActor
     *
     * @return int
     */
    public function getZooniverseActorAttribute()
    {
        return $this->getRelationValue('zooniverseActor')->first();
    }

    /**
     * GeoLocate actor relation.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function geoLocateActor(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        $pivot = [
            'id',
            'expedition_id',
            'actor_id',
            'state',
            'total',
            'error',
            'order',
            'expert'
        ];

        return $this->belongsToMany(Actor::class, 'actor_expedition')
            ->withPivot($pivot)
            ->wherePivot('actor_id', config('geolocate.actor_id'));
    }

    /**
     * Return geoLocateActor attribute.
     * $expedition->geoLocateActor
     *
     * @return int
     */
    public function getGeoLocateActorAttribute()
    {
        return $this->getRelationValue('geoLocateActor')->first();
    }

    /**
     * PanoptesProject
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function panoptesProject(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PanoptesProject::class);
    }

    /**
     * PanoptesTranscription relation.
     *
     * @return \MongoDB\Laravel\Relations\HasMany
     */
    public function panoptesTranscriptions(): \MongoDB\Laravel\Relations\HasMany
    {
        return $this->hasMany(PanoptesTranscription::class, 'subject_expeditionId');
    }

    /**
     * Relation used for wedigbio dashboard.
     *
     * @return \MongoDB\Laravel\Relations\HasMany
     */
    public function dashboard(): \MongoDB\Laravel\Relations\HasMany
    {
        return $this->hasMany(PusherTranscription::class, 'expedition_id');
    }

    /**
     * ExportQueue relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function exportQueue(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ExportQueue::class);
    }

    /**
     * GeoLocateExport relation with mongodb
     *
     * @return \MongoDB\Laravel\Relations\HasMany
     */
    public function geoLocateExports(): \MongoDB\Laravel\Relations\HasMany
    {
        return $this->hasMany(GeoLocateExport::class, 'subject_expeditionId');
    }

    /**
     * GeoLocateForm relation in mysql.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function geoLocateForm(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GeoLocateForm::class);
    }

    /**
     * GeoLocateDataSource relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function geoLocateDataSource(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(GeoLocateDataSource::class);
    }
}
