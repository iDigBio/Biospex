<?php namespace App\Models;

use Jenssegers\Eloquent\Model as Eloquent;
use Codesleeve\Stapler\ORM\EloquentTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;
use App\Models\Traits\UuidTrait;
use App\Models\Traits\BelongsToGroupTrait;
use App\Models\Traits\HasOneHeaderTrait;
use App\Models\Traits\HasManyExpeditionsTrait;
use App\Models\Traits\HasManySubjectsTrait;
use App\Models\Traits\HasManyMetasTrait;
use App\Models\Traits\HasManyImportsTrait;
use App\Models\Traits\HasManyOcrQueuesTrait;
use App\Models\Traits\HasManyUserGridFieldTrait;
use Illuminate\Support\Facades\Input;

class Project extends Eloquent implements StaplerableInterface, SluggableInterface
{
    use EloquentTrait;
    use SoftDeletes;
    use SluggableTrait;
    use UuidTrait;
    use BelongsToGroupTrait;
    use HasOneHeaderTrait;
    use HasManyExpeditionsTrait;
    use HasManySubjectsTrait;
    use HasManyMetasTrait;
    use HasManyImportsTrait;
    use HasManyOcrQueuesTrait;
    use HasManyUserGridFieldTrait;

    /**
     * Sluggable trait variable.
     * @var array
     */
    protected $sluggable = [
        'build_from' => 'title',
        'save_to'    => 'slug',
    ];

    /**
     * Protect dates columns.
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'projects';

    /**
     * Set database connection.
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * Set primary key of table.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Fillable columns.
     * @var array
     */
    protected $fillable = [
        'uuid',
        'group_id',
        'title',
        'slug',
        'contact',
        'contact_email',
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
        'banner',
        'target_fields',
        'advertise',
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('logo', ['styles' => ['thumb' => '100x67']]);
        $this->hasAttachedFile('banner', ['styles' => ['thumb' => '200x50']]);

        parent::__construct($attributes);
    }

    /**
     * Boot function to add model events
     */
    public static function boot()
    {
        parent::boot();

        static::bootStapler();

        static::saving(function ($model) {
            $model->target_fields = \Input::all();
        });

        // Delete associated subjects from expedition_subjects
        static::deleting(function ($model) {
            $model->expeditions()->delete();
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function expeditions()
    {
        return $this->hasMany('App\Models\Expedition');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function actors()
    {
        return $this->belongsToMany('App\Models\Actor')->withPivot('order_by')->orderBy('order_by', 'asc')->withTimestamps();
    }


    /**
     * Get project by slug
     *
     * @param $slug
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function bySlug($slug)
    {
        return $this->with(['group', 'expeditions.actorsCompletedRelation', 'expeditions.expeditionActors'])->where('slug', '=', $slug)->first();
    }

    /**
     * Find by uuid.
     *
     * @param $uuid
     * @return mixed
     */
    public function findByUuid($uuid)
    {
        return $this->where('uuid', pack('H*', str_replace('-', '', $uuid)))->get();
    }

    /**
     * Mutator for target_fields
     *
     * @param $input
     */
    public function setTargetFieldsAttribute($input)
    {
        $target_fields = [];

        if (isset($input['targetCount']) && $input['targetCount'] > 0) {
            for ($i=0; $i<$input['targetCount']; $i++) {
                if (empty($input['target_name'][$i])) {
                    continue;
                }

                $fields = [
                    'target_core'               => $input['target_core'][$i],
                    'target_name'               => $input['target_name'][$i],
                    'target_description'        => $input['target_description'][$i],
                    'target_valid_response'     => $input['target_valid_response'][$i],
                    'target_inference'          => $input['target_inference'][$i],
                    'target_inference_example'  => $input['target_inference_example'][$i],
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
     * Accessor for target_fields
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
        $ppsrFields = \Config::get('config.ppsr');
        foreach ($ppsrFields as $field => $data) {
            foreach ($data as $type => $value) {
                if ($type == 'private') {
                    $build[$field] = $this->{$value};
                }

                if ($type == 'date') {
                    $build[$field] = format_date($this->{$value}, 'Y-m-d m:d:s');
                }

                if ($type == 'column') {
                    $build[$field] = $input[$value];
                    continue;
                }

                if ($type == 'value') {
                    $build[$field] = $value;
                    continue;
                }

                if ($type == 'array') {
                    $combined = '';
                    foreach ($value as $col) {
                        $combined .= $input[$col] . ", ";
                    }
                    $build[$field] = rtrim($combined, ', ');
                    continue;
                }

                if ($type == 'url') {
                    if ($value == 'slug') {
                        $build[$field] = env('APP_URL') . '/' . $this->{$value};
                        continue;
                    }

                    if ($value == 'logo') {
                        $build[$field] = env('APP_URL') . $this->{$value}->url();
                        continue;
                    }
                }
            }
        }

        $advertise = ! empty($extra) ? array_merge($build, $extra) : $build;

        $this->attributes['advertise'] = json_encode($advertise, JSON_UNESCAPED_UNICODE);
    }
}
