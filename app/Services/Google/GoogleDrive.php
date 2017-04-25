<?php

namespace App\Services\Google;

class GoogleDrive extends GoogleBaseService
{
    /**
     * @var \Google_Service_Drive
     */
    protected $driveService;

    /**
     * GoogleFusionTable constructor.
     */
    public function __construct()
    {
        $this->driveService = $this->makeService('drive');
    }

    /**
     * @param $tableId
     * @param $property
     */
    public function createTablePermissions($tableId, $property)
    {
        $permission = $this->setServiceProperties('Drive_Permission', $property);
        $this->driveService->permissions->create($tableId, $permission);
    }
}