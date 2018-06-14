<?php

namespace App\Models;

use App\Facades\DateHelper;
use App\Presenters\ProjectPresenter;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Illuminate\Support\Facades\Config;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use App\Models\Traits\UuidTrait;
use McCool\LaravelAutoPresenter\HasPresenter;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Project extends Model implements AttachableInterface, HasPresenter
{
    use PaperclipTrait, Sluggable, UuidTrait, HybridRelations, LadaCacheTrait;

    /**
     * @inheritDoc
     */
    protected $table = 'projects';

    /**
     * @inheritDoc
     */
    protected $connection = 'mysql';

    /**
     * @inheritDoc
     */
    protected $primaryKey = 'id';

    /**
     * @inheritDoc
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
        'workflow_id',
        'logo',
        'banner',
        'target_fields',
        'status',
        'advertise',
        'fusion_table_id',
        'fusion_style_id',
        'fusion_template_id',
    ];

    /**
     * Project constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('logo', ['variants' => ['thumb' => '100x67', 'avatar' => '32x32']]);
        $this->hasAttachedFile('banner', ['variants' => ['thumb' => '200x50', 'carousel' => '650x225']]);

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
     * Get Resource Presenter.
     *
     * @return string
     */
    public function getPresenterClass()
    {
        return ProjectPresenter::class;
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title',
            ],
        ];
    }

    /**
     * Group relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    /**
     * Workflow relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Header relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function header()
    {
        return $this->hasOne(Header::class);
    }

    /**
     * Expedition relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function expeditions()
    {
        return $this->hasMany(Expedition::class);
    }

    /**
     * Subject relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * Meta relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function metas()
    {
        return $this->hasMany(Meta::class);
    }

    /**
     * Imports relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function imports()
    {
        return $this->hasMany(Import::class);
    }

    /**
     * OcrQueue relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ocrQueue()
    {
        return $this->hasMany(OcrQueue::class);
    }

    /**
     * AmChart relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function amChart()
    {
        return $this->hasOne(AmChart::class);
    }

    /**
     * NfnWorkflow relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function nfnWorkflows()
    {
        return $this->hasMany(NfnWorkflow::class);
    }

    /**
     * Events relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function panoptesTranscriptions()
    {
        return $this->hasMany(PanoptesTranscription::class, 'subject_projectId');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transcriptionLocations()
    {
        return $this->hasMany(TranscriptionLocation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function resources()
    {
        return $this->hasMany(ProjectResource::class);
    }

    /**
     * NfnClassificationsEarliestFinishedAtDate attribute.
     *
     * @return int
     */
    public function getEarliestFinishedAtDateAttribute()
    {
        $related = $this->getRelationValue('classificationsEarliestFinishedAtDate')->first();

        return $related ? $related->earliest_finished_at_date : null;
    }

    /**
     * Get earliest last finished_at date
     *
     * @return mixed
     */
    public function getTranscriptionsEarliestFinishedAtDate()
    {
        return $this->hasMany(PanoptesTranscription::class)->min('classification_finished_at');
    }

    /**
     * Set tag uri for rfc 4151 specs.
     *
     * @return string
     */
    public function setTagUriAttribute($input)
    {
        return 'tag:'.$_ENV['site.domain'].','.date('Y-m-d').':'.$this->attributes['slug'];
    }

    /**
     * Mutator for target_fields.
     *
     * @param $input
     */
    public function setTargetFieldsAttribute($input)
    {
        $target_fields = [];

        if (isset($input['targetCount']) && $input['targetCount'] > 0) {
            for ($i = 0; $i < $input['targetCount']; $i++) {
                if (empty($input['target_name'][$i])) {
                    continue;
                }

                $fields = [
                    'target_core'              => $input['target_core'][$i],
                    'target_name'              => $input['target_name'][$i],
                    'target_description'       => $input['target_description'][$i],
                    'target_valid_response'    => $input['target_valid_response'][$i],
                    'target_inference'         => $input['target_inference'][$i],
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
     * @param $value
     * @return mixed
     */
    public function getTargetFieldsAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * Set attribute for advertise.
     *
     * @param $input
     */
    public function setAdvertiseAttribute($input)
    {
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
                        $combined .= $input[$col].", ";
                    }
                    $build[$field] = rtrim($combined, ', ');
                    continue;
                }

                if ($type === 'url') {
                    if ($value === 'slug') {
                        $build[$field] = $_ENV['APP_URL'].'/'.$this->{$value};
                        continue;
                    }

                    if ($value === 'logo') {
                        $build[$field] = $_ENV['APP_URL'].$this->{$value}->url();
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
     * @param $value
     * @return mixed
     */
    public function getAdvertiseAttribute($value)
    {
        return unserialize($value);
    }
}
