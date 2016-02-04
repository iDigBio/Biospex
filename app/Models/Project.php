<?php namespace App\Models;

use Illuminate\Support\Facades\Config;
use Jenssegers\Eloquent\Model as Eloquent;
use Codesleeve\Stapler\ORM\EloquentTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;
use App\Models\Traits\UuidTrait;
use Illuminate\Support\Facades\Input;
use Symfony\Component\Console\Helper\Helper;

class Project extends Eloquent implements StaplerableInterface, SluggableInterface
{
    use EloquentTrait;
    use SoftDeletes;
    use SluggableTrait;
    use UuidTrait;

    /**
     * Sluggable value.
     *
     * @var array
     */
    protected $sluggable = [
        'build_from' => 'title',
        'save_to'    => 'slug',
    ];

    /**
     * Dates to protect.
     *
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
     * Database connection.
     *
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * Primary key of the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
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

        static::creating(function ($model) {
            $model->advertise = $model->attributes;
        });

        static::updating(function ($model) {
            $model->advertise = $model->attributes;
        });

        // Delete associated subjects from expedition_subjects
        static::deleting(function ($model) {
            $model->expeditions()->delete();
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function header()
    {
        return $this->hasOne(Header::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function expeditions()
    {
        return $this->hasMany(Expedition::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function metas()
    {
        return $this->hasMany(Meta::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function imports()
    {
        return $this->hasMany(Import::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ocrQueue()
    {
        return $this->hasMany(OcrQueue::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userGridField()
    {
        return $this->hasMany(UserGridField::class);
    }

    /**
     * Get project by slug
     *
     * @param $slug
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function bySlug($slug)
    {
        return $this->with(['group', 'expeditions.actorsCompletedRelation', 'expeditions.actors'])->where('slug', '=', $slug)->first();
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
     * Set tag uri for rfc 4151 specs.
     *
     * @return string
     */
    public function setTagUriAttribute($input)
    {
        return 'tag:' . $_ENV['site.domain'] . ',' . date('Y-m-d') . ':' . $this->attributes['slug'];
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
        $ppsrFields = Config::get('config.ppsr');

        foreach ($ppsrFields as $field => $data) {
            foreach ($data as $type => $value) {
                if ($type == 'private') {
                    $build[$field] = $this->{$value};
                }

                if ($type == 'date') {
                    $build[$field] = isset($this->{$value}) ?
                        format_date($this->{$value}, 'Y-m-d m:d:s') : format_date(null);
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
                        $build[$field] = $_ENV['APP_URL'] . '/' . $this->{$value};
                        continue;
                    }

                    if ($value == 'logo') {
                        $build[$field] = $_ENV['APP_URL'] . $this->{$value}->url();
                        continue;
                    }
                }
            }
        }

        $advertise = ! empty($extra) ? array_merge($build, $extra) : $build;

        $this->attributes['advertise'] = serialize($advertise);
    }

    public function getAdvertiseAttribute($value)
    {
        return unserialize($value);
    }

    public function getSubjectsAssignedCount($project)
    {
        return $project->subjectsAssignedCount;
    }

    /**
     * Get counts attribute
     *
     * @return int
     */
    public function getSubjectsAssignedCountAttribute()
    {
        return $this->subjects()->whereRaw(['expedition_ids' => ['$not' => ['$size' => 0]]])->get()->count();
    }
}
