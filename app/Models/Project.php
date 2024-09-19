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

use App\Facades\DateHelper;
use App\Models\Traits\Presentable;
use App\Models\Traits\UuidTrait;
use App\Presenters\ProjectPresenter;
use Cviebrock\EloquentSluggable\Sluggable;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Config;
use MongoDB\Laravel\Eloquent\HybridRelations;

/**
 * Class Project
 */
class Project extends BaseEloquentModel implements AttachableInterface
{
    use HasFactory, HybridRelations, PaperclipTrait, Presentable, Sluggable, UuidTrait;

    /**
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * {@inheritDoc}
     */
    protected $table = 'projects';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'uuid',
        'group_id',
        'title',
        'slug',
        'contact',
        'contact_email',
        'contact_title',
        'organization_website',
        'organization',
        'project_partners',
        'funding_source',
        'description_short',
        'description_long',
        'incentives',
        'geographic_scope',
        'taxonomic_scope',
        'temporal_scope',
        'keywords',
        'blog_url',
        'facebook',
        'twitter',
        'activities',
        'language_skills',
        'logo',
        'banner_file',
        'target_fields',
        'status',
        'advertise',
        'geolocate_community',
    ];

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
     * @var string
     */
    protected $presenter = ProjectPresenter::class;

    /**
     * Project constructor.
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('logo', [
            'url' => config('config.missing_project_logo'),
        ]);

        parent::__construct($attributes);
    }

    /**
     * Boot function to add model events
     */
    public static function boot()
    {
        parent::boot();

        static::bootUuidTrait();

        static::creating(function ($model) {
            $model->advertise = $model->attributes;
        });

        static::updating(function ($model) {
            $model->advertise = $model->attributes;
        });
    }

    /**
     * Return the sluggable configuration array for this model test.
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
            ],
        ];
    }

    /**
     * AmChart relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function amChart()
    {
        return $this->hasOne(AmChart::class);
    }

    /**
     * Bingos relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bingos()
    {
        return $this->hasMany(Bingo::class);
    }

    /**
     * Events relations.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Expedition relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function expeditions()
    {
        return $this->hasMany(Expedition::class);
    }

    /**
     * Expedition Stat relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function expeditionStats()
    {
        return $this->hasManyThrough(ExpeditionStat::class, Expedition::class);
    }

    /**
     * GeoLocateCommunity relation.
     */
    public function geoLocateCommunity(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GeoLocateCommunity::class);
    }

    /**
     * Group relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    /**
     * Header relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function header()
    {
        return $this->hasOne(Header::class);
    }

    /**
     * Imports relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function imports()
    {
        return $this->hasMany(Import::class);
    }

    /**
     * Meta relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function metas()
    {
        return $this->hasMany(Meta::class);
    }

    /**
     * OcrQueue relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ocrQueue()
    {
        return $this->hasMany(OcrQueue::class);
    }

    /**
     * PanoptesProject relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function panoptesProjects()
    {
        return $this->hasMany(PanoptesProject::class);
    }

    /**
     * PanoptesProject relation returning last in database.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lastPanoptesProject()
    {
        return $this->hasOne(PanoptesProject::class)->latest();
    }

    /**
     * Panoptes transcription relation.
     */
    public function panoptesTranscriptions(): \MongoDB\Laravel\Relations\HasMany
    {
        return $this->hasMany(PanoptesTranscription::class, 'subject_projectId');
    }

    /**
     * Resources relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function resources()
    {
        return $this->hasMany(ProjectResource::class);
    }

    /**
     * Subject relation.
     */
    public function subjects(): \MongoDB\Laravel\Relations\HasMany
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * Transcription location relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transcriptionLocations()
    {
        return $this->hasMany(TranscriptionLocation::class);
    }

    /**
     * Workflow Manager relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function workflowManagers()
    {
        return $this->hasManyThrough(WorkflowManager::class, Expedition::class);
    }

    /**
     * Set tag uri for rfc 4151 specs.
     *
     * @return string
     */
    public function setTagUriAttribute()
    {
        return 'tag:'.$_ENV['site.domain'].','.date('Y-m-d').':'.$this->attributes['slug'];
    }

    /**
     * Mutator for target_fields.
     */
    public function setTargetFieldsAttribute($input)
    {
        if (empty($input)) {
            return;
        }

        $target_fields = [];

        if (isset($input['targetCount']) && $input['targetCount'] > 0) {
            for ($i = 0; $i < $input['targetCount']; $i++) {
                if (empty($input['target_name'][$i])) {
                    continue;
                }

                $fields = [
                    'target_core' => $input['target_core'][$i],
                    'target_name' => $input['target_name'][$i],
                    'target_description' => $input['target_description'][$i],
                    'target_valid_response' => $input['target_valid_response'][$i],
                    'target_inference' => $input['target_inference'][$i],
                    'target_inference_example' => $input['target_inference_example'][$i],
                ];
                $target_fields[$i] = $fields;
            }
        } else {
            unset($input['target_name']);
            unset($input['target_description']);
            unset($input['target_valid_response']);
            unset($input['target_inference']);
            unset($input['target_inference_example']);
        }
        $this->attributes['target_fields'] = (! empty($target_fields)) ? json_encode($target_fields) : '';
    }

    /**
     * Accessor for target_fields.
     *
     * @return mixed
     */
    public function getTargetFieldsAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * Set attribute for advertise.
     */
    public function setAdvertiseAttribute($input)
    {
        if (empty($input)) {
            return;
        }

        $extra = isset($input['advertiseExtra']) ? $input['advertiseExtra'] : '';

        $build = [];
        $ppsrFields = Config::get('config.ppsr');

        foreach ($ppsrFields as $field => $data) {
            foreach ($data as $type => $value) {
                if ($type === 'private') {
                    $build[$field] = $this->{$value};
                }

                if ($type === 'date') {
                    $build[$field] = isset($this->{$value}) ? DateHelper::formatDate($this->{$value}, 'Y-m-d m:d:s') : DateHelper::formatDate(null);
                }

                if ($type === 'column') {
                    $build[$field] = $input[$value];

                    continue;
                }

                if ($type === 'value') {
                    $build[$field] = $value;

                    continue;
                }

                if ($type === 'array') {
                    $combined = '';
                    foreach ($value as $col) {
                        $combined .= $input[$col].', ';
                    }
                    $build[$field] = rtrim($combined, ', ');

                    continue;
                }

                if ($type === 'url') {
                    if ($value === 'slug') {
                        $build[$field] = config('app.url').'/'.$this->{$value};

                        continue;
                    }

                    if ($value === 'logo') {
                        $build[$field] = config('app.url').$this->{$value}->url();

                        continue;
                    }
                }
            }
        }

        $advertise = ! empty($extra) ? array_merge($build, $extra) : $build;

        $this->attributes['advertise'] = serialize($advertise);
    }

    /**
     * Advertise attribute.
     *
     * @return mixed
     */
    public function getAdvertiseAttribute($value)
    {
        return unserialize($value);
    }
}
