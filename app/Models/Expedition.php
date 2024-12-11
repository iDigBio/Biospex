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

use App\Models\Traits\Presentable;
use App\Models\Traits\UuidTrait;
use App\Presenters\ExpeditionPresenter;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\HybridRelations;

/**
 * Class Expedition
 */
class Expedition extends BaseEloquentModel implements AttachableInterface
{
    use HasFactory, HybridRelations, PaperclipTrait, Presentable, UuidTrait;

    protected $table = 'expeditions';

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
        'locked',
    ];

    protected string $presenter = ExpeditionPresenter::class;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Model Boot
     */
    public static function boot(): void
    {
        parent::boot();
        static::bootUuidTrait();
    }

    /**
     * Expedition constructor.
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('logo', [
            'variants' => [
                'medium' => [
                    'resize' => ['dimensions' => '318x208'],
                ],
            ],
            'url' => config('config.missing_expedition_logo'),
            'urls' => [
                // This fallback URL is only given for the 'thumb' variant.
                'medium' => config('config.missing_expedition_logo'),
            ],
        ]);
        //$this->hasAttachedFile('logo', ['resize' => ['dimensions' => '318x208']]);

        parent::__construct($attributes);
    }

    /**
     * Relation used for wedigbio dashboard.
     */
    public function dashboard(): \MongoDB\Laravel\Relations\HasMany
    {
        return $this->hasMany(PusherTranscription::class, 'expedition_id');
    }

    /**
     * Download relation.
     */
    public function downloads(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Download::class);
    }

    /**
     * ExportQueue relation.
     */
    public function exportQueue(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ExportQueue::class);
    }

    /**
     * Download GeoLocate Export relation
     */
    public function geoLocateExport(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Download::class)->where('actor_id', config('geolocate.actor_id'))->where('type', 'export');
    }

    /**
     * Ocr Queue relation.
     */
    public function ocrQueue(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OcrQueue::class);
    }

    /**
     * Project relationship.
     */
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * ExpeditionStat relationship.
     */
    public function stat(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ExpeditionStat::class);
    }

    /**
     * Subject relationship.
     *  $expedition->subjects()->attach($subject) adds expedition ids in subjects
     */
    public function subjects(): \MongoDB\Laravel\Relations\BelongsToMany
    {
        return $this->belongsToMany(Subject::class);
    }

    /**
     * Workflow relation.
     */
    public function workflow(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * WorkflowManager relationship.
     */
    public function workflowManager(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(WorkflowManager::class);
    }

    /**
     * Download Zooniverse Export relation
     */
    public function zooniverseExport(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Download::class)->where('actor_id', config('zooniverse.actor_id'))->where('type', 'export');
    }

    /**
     * Actors relation.
     */
    public function actors(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Actor::class, 'actor_expedition')
            ->withPivot('id', 'expedition_id', 'actor_id', 'state', 'total', 'error', 'order', 'expert')
            ->orderBy('order')
            ->withTimestamps();
    }

    /**
     * ActorExpedition relation.
     */
    public function actorExpeditions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ActorExpedition::class);
    }

    /**
     * GeoActorExpedition relation.
     */
    public function geoActorExpedition(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ActorExpedition::class)->where('actor_id', config('geolocate.actor_id'));
    }

    /**
     * ZooActorExpedition relation.
     */
    public function zooActorExpedition(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ActorExpedition::class)->where('actor_id', config('zooniverse.actor_id'));
    }

    /**
     * PanoptesProject
     */
    public function panoptesProject(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PanoptesProject::class);
    }

    /**
     * GeoLocateForm relation in mysql.
     */
    public function geoLocateForm(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GeoLocateForm::class);
    }

    /**
     * GeoLocateDataSource relation.
     */
    public function geoLocateDataSource(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(GeoLocateDataSource::class);
    }
}
