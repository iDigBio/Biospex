<?php

namespace App\Services;

use App\Repositories\Interfaces\RapidHeader;

class RapidExportService
{
    /**
     * @var \App\Repositories\Interfaces\RapidHeader
     */
    private $rapidHeaderInterface;

    /**
     * RapidExportService constructor.
     *
     * @param \App\Repositories\Interfaces\RapidHeader $rapidHeaderInterface
     */
    public function __construct(RapidHeader $rapidHeaderInterface)
    {

        $this->rapidHeaderInterface = $rapidHeaderInterface;
    }

    /**
     * Get header.
     *
     * @return mixed
     */
    public function getHeader()
    {
        $protected = config('config.protectedFields');

        $rapidHeader = $this->rapidHeaderInterface->first();

        return collect($rapidHeader->header)->reject(function ($field) use($protected) {
            return in_array($field, $protected);
        });
    }
    /**
     * Map header columns to tags.
     *
     * @param $headers
     * @return \Illuminate\Support\Collection
     */
    public function mapColumns($headers)
    {
        $tags = config('config.updateColumnTags');

        $mapped = collect($headers)->mapToGroups(function($header) use($tags){
            foreach ($tags as $tag) {
                if (preg_match('/'.$tag.'/', $header, $matches)) {
                    return [$matches[0] => $header];
                }
            }
            return ['unused' => $header];
        });

        return $mapped->forget('unused');
    }

    /**
     * Create select for export columns.
     *
     * @param int $count
     * @return array|string
     * @throws \Throwable
     */
    public function createExportFieldSelect(int $count = 0)
    {
        $exportFields = json_decode(\Storage::get(config('config.exportFieldsFile')), true);

        return view('export.partials.export-field-select', compact('exportFields', 'count'))->render();
    }
}