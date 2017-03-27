<?php

namespace App\Services\Google;

use Config;

class Drive extends GoogleService
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
        $permission = $this->setServiceProperties('drive_permission', $property);
        $this->driveService->permissions->create($tableId, $permission);
    }
}