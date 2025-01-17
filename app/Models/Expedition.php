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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use MongoDB\Laravel\Eloquent\HybridRelations;

/**
 * Class Expedition
 *
 * Represents an expedition with various attributes and relationships,
 * encapsulating functionality for data management and workflow integrations.
 */
class Expedition extends BaseEloquentModel implements AttachableInterface
{
    use HasFactory, HybridRelations, PaperclipTrait, Presentable, UuidTrait;

    /**
     * The name of the database table associated with the model.
     *
     * @var string
     */
    protected $table = 'expeditions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
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
        'locked',
    ];

    /**
     * Holds the class name of the ExpeditionPresenter.
     */
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
     * Get the route key name for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Boot method to initialize the model's functionality and traits.
     */
    public static function boot(): void
    {
        parent::boot();
        static::bootUuidTrait();
    }

    /**
     * Constructor for initializing the model with specific attributes and configuring the attached file for 'logo'.
     *
     * @param  array  $attributes  An optional array of attributes to initialize the model with.
     * @return void
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
        // $this->hasAttachedFile('logo', ['resize' => ['dimensions' => '318x208']]);

        parent::__construct($attributes);
    }

    /**
     * Defines a one-to-many relationship with the PusherTranscription model.
     *
     * @return \MongoDB\Laravel\Relations\HasMany The associated collection of PusherTranscription instances.
     */
    public function dashboard(): \MongoDB\Laravel\Relations\HasMany
    {
        return $this->hasMany(PusherTranscription::class, 'expedition_id');
    }

    /**
     * Establishes a one-to-many relationship with the Download model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany The associated Download instances.
     */
    public function downloads(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Download::class);
    }

    /**
     * Establishes a one-to-one relationship with the Download model, filtered by actor ID from configuration and type 'csv'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne The associated Download instance that matches the specified conditions.
     */
    public function geoLocateCsvDownload(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Download::class)->where('actor_id', config('geolocate.actor_id'))->where('type', 'csv');
    }

    /**
     * Defines a one-to-one relationship with the ExportQueue model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne The related ExportQueue instance.
     */
    public function exportQueue(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ExportQueue::class);
    }

    /**
     * Establishes a one-to-one relationship with the Download model, specifically for export downloads
     * filtered by the configured actor ID and type 'export'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne The associated Download instance matching the export criteria.
     */
    public function geoLocateExport(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Download::class)->where('actor_id', config('geolocate.actor_id'))->where('type', 'export');
    }

    /**
     * Defines a one-to-many relationship with the OcrQueue model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany The related OcrQueue instances.
     */
    public function ocrQueue(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OcrQueue::class);
    }

    /**
     * Defines an inverse relationship with the Project model.
     *
     * @return BelongsTo The related Project instance.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Defines a one-to-one relationship with the ExpeditionStat model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne The associated ExpeditionStat instance.
     */
    public function stat(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ExpeditionStat::class);
    }

    /**
     * Defines a many-to-many relationship with the Subject model.
     *
     * @note $expedition->subjects()->attach($subject) adds expedition ids in subjects
     *
     * @return \MongoDB\Laravel\Relations\BelongsToMany The related Subject instances.
     */
    public function subjects(): \MongoDB\Laravel\Relations\BelongsToMany
    {
        return $this->belongsToMany(Subject::class);
    }

    /**
     * Defines an inverse one-to-many relationship with the Workflow model.
     *
     * @return BelongsTo The parent Workflow instance.
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Defines a one-to-one relationship with the WorkflowManager model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne The related WorkflowManager instance.
     */
    public function workflowManager(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(WorkflowManager::class);
    }

    /**
     * Establishes a one-to-one relationship with the Download model specifically for Zooniverse exports.
     * Applies filters based on actor ID from configuration and the type set to 'export'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne The associated Download instance for Zooniverse export.
     */
    public function zooniverseExport(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Download::class)->where('actor_id', config('zooniverse.actor_id'))
            ->where('type', 'export');
    }

    /**
     * Defines a many-to-many relationship with the Actor model via the actor_expedition pivot table.
     * Includes additional pivot attributes and timestamps, and orders the results by the 'order' column.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany The related Actor instances with pivot data.
     */
    public function actors(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Actor::class, 'actor_expedition')
            ->withPivot('id', 'expedition_id', 'actor_id', 'state', 'total', 'error', 'order', 'expert')
            ->orderBy('order')
            ->withTimestamps();
    }

    /**
     * Defines a one-to-many relationship with the ActorExpedition model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany The related ActorExpedition instances.
     */
    public function actorExpeditions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ActorExpedition::class);
    }

    /**
     * Establishes a one-to-one relationship with the ActorExpedition model, filtered by the configured actor ID.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne The associated ActorExpedition instance.
     */
    public function geoActorExpedition(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ActorExpedition::class)->where('actor_id', config('geolocate.actor_id'));
    }

    /**
     * Establishes a one-to-one relationship with the ActorExpedition model, filtered by the specified actor ID.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne The associated ActorExpedition instance matching the actor ID.
     */
    public function zooActorExpedition(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ActorExpedition::class)->where('actor_id', config('zooniverse.actor_id'));
    }

    /**
     * Defines a one-to-one relationship with the PanoptesProject model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne The related PanoptesProject instance.
     */
    public function panoptesProject(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PanoptesProject::class);
    }

    /**
     * Defines a has-one-through relationship with the GeoLocateForm model through the GeoLocateDataSource model.
     *
     * @return HasOneThrough The related GeoLocateForm instance accessed through GeoLocateDataSource.
     */
    public function geoLocateForm(): HasOneThrough
    {
        return $this->hasOneThrough(GeoLocateForm::class, GeoLocateDataSource::class,
            'expedition_id', // Foreign key on GeoLocateDataSource table
            'id', // Foreign key on GeoLocateForm table
            'id', // Local key on the Expedition model
            'geo_locate_form_id' // Local key on the GeoLocateDataSource model
        );
    }

    /**
     * Establishes a one-to-one relationship through the GeoLocateDataSource model to the GeoLocateCommunity model.
     *
     * @return HasOneThrough The associated GeoLocateCommunity instance accessed through GeoLocateDataSource.
     */
    public function geoLocateCommunity(): HasOneThrough
    {
        return $this->hasOneThrough(GeoLocateCommunity::class, GeoLocateDataSource::class, 'expedition_id',
            // Foreign key on GeoLocateDataSource table
            'id', // Foreign key on GeoLocateForm table
            'id', // Local key on the Expedition model
            'geo_locate_community_id' // Local key on the GeoLocateDataSource model
        );
    }

    /**
     * Establishes a one-to-one relationship with the GeoLocateDataSource model.
     *
     * @return HasOne The associated GeoLocateDataSource instance.
     */
    public function geoLocateDataSource(): HasOne
    {
        return $this->hasOne(GeoLocateDataSource::class);
    }
}
