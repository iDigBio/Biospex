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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Jenssegers\Mongodb\Eloquent\HybridRelations;
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
     * @var string
     */
    protected $connection = 'mysql';

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
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * ExpeditionStat relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function stat()
    {
        return $this->hasOne(ExpeditionStat::class);
    }

    /**
     * Return expedition stat transcriptions have started.
     *
     * @return mixed
     */
    public function statWithTranscriptions()
    {
        return $this->hasOne(ExpeditionStat::class)->where('transcriptions_completed', '>', 0);
    }

    /**
     * Subject relationship.
     * $expedition->subjects()->attach($subject) adds expedition ids in subjects
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subjects(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Subject::class);
    }

    /**
     * Workflow relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\Jenssegers\Mongodb\Relations\BelongsTo
     */
    public function workflow(): \Illuminate\Database\Eloquent\Relations\BelongsTo|\Jenssegers\Mongodb\Relations\BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * WorkflowManager relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function workflowManager()
    {
        return $this->hasOne(WorkflowManager::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function downloads()
    {
        return $this->hasMany(Download::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function export(): HasOne
    {
        return $this->hasOne(Download::class)->where('type', 'export');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ocrQueue()
    {
        return $this->hasMany(OcrQueue::class);
    }

    /**
     * @return mixed
     */
    public function actors()
    {
        return $this->belongsToMany(Actor::class, 'actor_expedition')
            ->withPivot('id', 'expedition_id', 'actor_id', 'state', 'total', 'error', 'order', 'expert')
            ->orderBy('order')
            ->withTimestamps();
    }

    /**
     * @return mixed
     */
    public function nfnActor()
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
            ->wherePivot('actor_id', config('config.nfnActorId'));
    }

    public function geoLocateActor()
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
            ->wherePivot('actor_id', config('config.geoLocateActorId'));
    }

    /**
     * Return nfnActor attribute.
     * $expedition->nfnActor
     *
     * @return int
     */
    public function getNfnActorAttribute()
    {
        return $this->getRelationValue('nfnActor')->first();
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
    public function panoptesProject()
    {
        return $this->hasOne(PanoptesProject::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function panoptesTranscriptions()
    {
        return $this->hasMany(PanoptesTranscription::class, 'subject_expeditionId');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dashboard()
    {
        return $this->hasMany(PusherTranscription::class, 'expedition_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function exportQueue()
    {
        return $this->hasOne(ExportQueue::class);
    }

    /**
     * GeoLocate relation with mongodb
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\Jenssegers\Mongodb\Relations\HasMany
     */
    public function geoLocate(): \Illuminate\Database\Eloquent\Relations\HasMany|\Jenssegers\Mongodb\Relations\HasMany
    {
        return $this->hasMany(GeoLocate::class, 'subject_expeditionId');
    }

    /**
     * GeoLocateForm relation in mysql.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function geoLocateForm(): HasOne
    {
        return $this->hasOne(GeoLocateForm::class);
    }

    /**
     * Get counts attribute.
     *
     * @return int
     */
    public function getSubjectsCountAttribute()
    {
        return $this->subjects()->count();
    }

}
