<?php

namespace App\Services\Google;


use Illuminate\Support\Collection;

class Bucket extends GoogleService
{

    /**
     * @var array
     */
    public $quartiles = [
        'firstQuartile',
        'secondQuartile',
        'thirdQuartile',
        'interQuartile',
        'outlierLimit',
        'aboveOutlierLimit'
    ];

    public $colors = [
        '#0000ff',
        '#33ffff',
        '#ffff00',
        '',
        '#ff6600',
        '#ff0000'
    ];

    /**
     * @var
     */
    public $first;

    /**
     * @var
     */
    public $second;

    /**
     * @var
     */
    public $third;

    /**
     * @var
     */
    public $inter;

    /**
     * @var
     */
    public $outlier;

    /**
     * @var
     */
    public $lastMax;

    /**
     * @var array
     */
    public $buckets = [];

    /**
     * @param $buckets
     * @return Collection
     */
    public function fusionTableBuckets($buckets)
    {
        return $buckets->filter(function($bucket){
            return $bucket !== null;
        })->map(function ($bucket)
        {
            return $this->setServiceProperties('fusiontables_bucket', $bucket);
        });
    }

    /**
     * Create Buckets.
     *
     * @param $counts
     * @return Collection
     */
    public function calculateBuckets($counts)
    {
        return collect($this->quartiles)->map(function ($quartile, $key) use ($counts)
        {
            $max = $this->{$quartile}($counts);
            $max = $quartile !== 'interQuartile' ? $max : $this->lastMax;
            $min = $quartile === 'firstQuartile' ? 0 : $this->lastMax;
            $this->lastMax = $max;
            return $quartile === 'interQuartile' ? null : [
                'setColor'   => $this->colors[$key],
                'setMin'     => $min,
                'setMax'     => $max,
                'setOpacity' => 0.5,
            ];
        });
    }

    /**
     * @param $array
     * @return mixed
     */
    public function firstQuartile($array)
    {
        return $this->first = $this->quartile($array, 0.25);
    }

    /**
     * @param $array
     * @return mixed
     */
    public function secondQuartile($array)
    {
        return $this->second = $this->quartile($array, 0.5);
    }

    /**
     * @param $array
     * @return mixed
     */
    public function thirdQuartile($array)
    {
        return $this->third = $this->quartile($array, 0.75);
    }

    /**
     * @param $array
     * @param $quartile
     * @return mixed
     */
    public function quartile($array, $quartile)
    {
        $pos = (count($array) - 1) * $quartile;

        $base = (int) floor($pos);
        $rest = $pos - $base;

        if (isset($array[$base + 1]))
        {
            return ceil($array[$base] + $rest * ($array[$base + 1] - $array[$base]));
        }

        return ceil($array[$base]);
    }

    /**
     * @return mixed
     */
    public function interQuartile()
    {
        return $this->inter = $this->third - $this->first;
    }

    /**
     * @return float
     */
    public function outlierLimit()
    {
        return $this->outlier = ceil($this->third + (1.5 * $this->inter));
    }

    /**
     * @return string
     */
    public function aboveOutlierLimit()
    {
        return 20000;
    }
}