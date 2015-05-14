<?php
/**
 * variables.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <bruhnrp@gmail.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */


return [

    /**
     * Site variables
     */
    'adminEmail'        => env('MAIL_ADDRESS'),
    'registration'      => env('APP_REGISTRATION'),
    'name'              => env('APP_NAME'),
    'translate'         => false,
    'dataDir'           => storage_path('data'),
    'dataTmp'           => storage_path('data/tmp'),
    'disableConfig'     => true,

    'ocrPostUrl'        => env('OCR_POSTURL'),
    'ocrGetUrl'         => env('OCR_GETURL'),
    'ocrDeleteUrl'      => env('OCR_DELETEURL'),
    'ocrCrop'           => env('OCR_CROP'),
    'disableOcr'        => env('OCR_DISABLE', false),

    'identifiers'       => [
        'identifier',
        'providerManagedID',
        'uuid',
        'recordId',
    ],

    'modelColumns'      => [
        'Assigned',
        'Id',
        'AccessURI',
        'Ocr'
    ],

    'selectColumns'     => [
        'expedition_ids',
        'id',
        'accessURI',
        'ocr'
    ],

    'statusSelect'      => [
        'starting' => 'Starting',
        'acting'   => 'Acting',
        'complete' => 'Complete',
        'hiatus'   => 'Hiatus'
    ],

    'ppsr'              => [
        'ProjectGUID'             => ['private' => 'uuid'],
        'ProjectName'             => ['column' => 'title'],
        'ProjectDataProvider'     => ['value' => env('APP_NAME')],
        'ProjectDescription'      => ['column' => 'description_long'],
        'ProjectDateLastUpdated'  => ['private' => 'updated_at'],
        'ProjectContactName'      => ['column' => 'contact'],
        'ProjectContactEmail'     => ['column' => 'contact_email'],
        'ProjectStatus'           => ['column' => 'status'],
        'ProjectOrganization'     => ['column' => 'organization'],
        'ProjectVolunteerSupport' => ['column' => 'incentives'],
        'ProjectURL'              => ['url' => 'slug'],
        'ProjectFacebook'         => ['column' => 'facebook'],
        'ProjectTwitter'          => ['column' => 'twitter'],
        'ProjectKeywords'         => ['array' => ['keywords', 'geographic_scope', 'temporal_scope']],
        'fieldOfScience'          => [],
        'participationType'       => [],
        'participantEducation'    => ['column' => 'language_skills'],
        'fundingSource'           => ['column' => 'funding_source'],
        'projectBlog'             => ['column' => 'blog_url'],
        'projectImage'            => ['url' => 'logo'],
    ],

    'images'            => [
        'thumbDefaultImg'    => '/assets/default_image.jpg',
        'thumbOutputDir'     => storage_path() . '/images',
        'thumbWidth'         => 150,
        'thumbHeight'        => 150,
        'library'            => 'gmagick',
        'quality'            => 100,
        'imageTypeExtension' => [
            'image/jpeg' => "jpg",
            'image/png'  => "png",
            'image/tiff' => "tif",
            'image/gif'  => "gif"
        ],
    ],

    /** Min and max logo and banner sizes used in Project model for Codesleve Stapler */
    'logo'              => '300x200',
    'banner'            => '1200x300',

    /* Added Tubes */
    'tubes'        => [
        'default'         => env('QUEUE_DEFAULT_TUBE'),
        'subjectsImport'  => env('QUEUE_SUBJECTS_TUBE'),
        'workflowManager' => env('QUEUE_WORKFLOW_TUBE'),
        'ocr'             => env('QUEUE_OCR_TUBE')
    ],

    'group_permissions' => [
        "project_create"    => 1,
        "project_edit"      => 1,
        "project_view"      => 1,
        "project_delete"    => 1,
        "group_create"      => 1,
        "group_edit"        => 1,
        "group_view"        => 1,
        "group_delete"      => 1,
        "expedition_create" => 1,
        "expedition_edit"   => 1,
        "expedition_view"   => 1,
        "expedition_delete" => 1
    ],
];