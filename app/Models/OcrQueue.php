<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UuidTrait;

class OcrQueue extends Model
{
    use UuidTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ocr_queues';

    /**
     * @var array
     */
    protected $fillable = [
        'project_id',
        'ocr_csv_id',
        'uuid',
        'data',
        'subject_count',
        'subject_remaining',
        'tries',
        'batch',
        'status',
        'error',
        'attachments'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ocrCsv()
    {
        return $this->belongsTo(OcrCsv::class);
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

    public function findByProjectId($id){
        return $this->whereProjectId($id)->first();
    }

    public function findFirstWith($with)
    {
        $query = $this->with($with);
        return $query->where('error', 0)->where('status', '<', 2)->orderBy('id', 'asc')->first();
    }

    public function findAllWith($with)
    {
        $query = $this->with($with);
        return $query->where('error', 0)->where('status', '<', 2)->orderBy('id', 'asc')->get();
    }

    public function getSubjectRemainingSum($id)
    {
        return (int) $this->where('id', '<', $id)->sum('subject_remaining');
    }
}
