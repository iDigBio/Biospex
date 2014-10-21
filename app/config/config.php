<?php

return array(

    /** Set Admin email */
    'adminEmail' => 'biospex@gmail.com',

    /** Used in code. Different than application debug in app.php */
    'debug' => false,

    /** Default project image path */
    'defaultImg' => 'assets/default.png',

    /** Turn on language translation in main menu */
    'translate' => false,

    /** Default expedition data directories */
    'dataDir' => storage_path() . '/data',
    'dataTmp' => storage_path() . '/data/tmp',

	/** Imagine settings */
	'library' => 'imagick',
	'quality' => 100,

    /** Min and max logo and banner sizes used in Project model for Codesleve Stapler */
    'logo' => '300x200',
    'banner' => '1200x300',

	'identifiers' => [
		'identifier',
		'providerManagedID',
		'uuid',
		'recordId',
	],

    /**
     * Default group permissions
     */

	'group_permissions' => [
        "project_create" => 1,
        "project_edit" => 1,
        "project_view" => 1,
        "project_delete" => 1,
        "group_create" => 1,
        "group_edit" => 1,
        "group_view" => 1,
        "group_delete" => 1,
        "expedition_create" => 1,
        "expedition_edit" => 1,
        "expedition_view" => 1,
        "expedition_delete" => 1
	],


    /**
     * Allow/Disallow registration
     */
    'registration' => true,
);