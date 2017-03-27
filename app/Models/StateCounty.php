<?php

namespace App\Models;

use Eloquent;

class StateCounty extends Eloquent
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'state_counties';

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