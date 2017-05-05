<?php

namespace App\Models;


class StateCounty extends BaseEloquentModel
{

    /**
     * @inheritDoc
     */
    protected $table = 'state_counties';

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
        'county_name',
        'state_county',
        'state_abbr',
        'state_abbr_cap',
        'geometry',
        'value',
        'geo_id',
        'geo_id_2',
        'geographic_name',
        'state_num',
        'county_num',
        'fips_forumla',
        'has_error',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transcriptionLocations()
    {
        return $this->hasMany(TranscriptionLocation::class, 'state_county', 'state_county');
    }
}