<?php namespace Biospex\Models;

use Illuminate\Database\Eloquent\Model;
use Biospex\Models\Traits\UuidTrait;

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

    public function findFirstQueueRecord($with)
    {
        $query = $this->with($with);
        return $query->where('status', null)->where('error', 0)->first();
    }

    public function getSubjectCountSum($id)
    {
        return $this->sum('subject_count')->where('id', '<', $id);
    }
}
