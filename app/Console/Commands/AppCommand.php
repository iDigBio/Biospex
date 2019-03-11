<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     */
    public function handle()
    {

    }

    public function data()
    {
        return [
            'project_id'                              => 40,
            'ocr'                                     => '',
            'expedition_ids'                          => [],
            'coreid'                                  => 'df6cfde3-1093-4352-8c86-c7faa354e7a5',
            'CountryName'                             => '',
            'LocationCreated'                         => '',
            'ProvinceState'                           => '',
            'WorldRegion'                             => '',
            'IDofContainingCollection'                => '',
            'accessURI'                               => 'http://www.ngpherbaria.org/imglib/ngreatplains/DEK/DEK000092/DEK000092883_1475611880_lg.jpg',
            'associatedSpecimenReference'             => 'http://midwestherbaria.org/portal/collections/individual/index.php?occid=11284153',
            'attributionLogoURL'                      => '',
            'bestQualityAccessURI'                    => '',
            'bestQualityFormat'                       => '',
            'caption'                                 => 'Dahlia scapigeroides',
            'captureDevice'                           => '',
            'comments'                                => '',
            'derivedFrom'                             => '',
            'digitizationDate'                        => '',
            'fundingAttribution'                      => '',
            'furtherInformationURL'                   => '',
            'goodQualityAccessURI'                    => 'http://www.ngpherbaria.org/imglib/ngreatplains/DEK/DEK000092/DEK000092883_1475611880.jpg',
            'hasServiceAccessPoint'                   => '',
            'hashFunction'                            => '',
            'hashValue'                               => '',
            'licenseLogoURL'                          => '',
            'metadataCreator'                         => '',
            'metadataLanguage'                        => 'en',
            'metadataLanguageLiteral'                 => '',
            'metadataProvider'                        => '',
            'metadataProviderLiteral'                 => '',
            'physicalSetting'                         => '',
            'provider'                                => '',
            'providerID'                              => '',
            'providerLiteral'                         => '',
            'providerManagedID'                       => 'urn:uuid:f92d8de2-bbd9-4fe0-81f5-e5a7bfe357cf',
            'resourceCreationTechnique'               => '',
            'serviceExpectation'                      => '',
            'subjectCategoryVocabulary'               => '',
            'subjectOrientation'                      => '',
            'subjectPart'                             => '',
            'subtype'                                 => 'Photograph',
            'subtypeLiteral'                          => '',
            'tag'                                     => '',
            'taxonCount'                              => '',
            'taxonCoverage'                           => '',
            'thumbnailAccessURI'                      => 'http://www.ngpherbaria.org/imglib/ngreatplains/DEK/DEK000092/DEK000092883_1475611880_tn.jpg',
            'variant'                                 => '',
            'variantLiteral'                          => '',
            'creator'                                 => '',
            'format'                                  => '',
            'language791b'                            => '',
            'rights'                                  => '',
            'source'                                  => '',
            'type109f'                                => '',
            'accessRights'                            => '',
            'audience'                                => '',
            'available'                               => '',
            'created'                                 => '',
            'creator4f55'                             => '',
            'description'                             => '',
            'format76d1'                              => 'image/jpeg',
            'identifier'                              => 'http://www.ngpherbaria.org/imglib/ngreatplains/DEK/DEK000092/DEK000092883_1475611880_lg.jpg',
            'language'                                => '',
            'license'                                 => '',
            'modified'                                => '',
            'publisher'                               => '',
            'references'                              => '',
            'rightsf2e8'                              => '',
            'rightsHolder'                            => '',
            'source6e38'                              => '',
            'title'                                   => '',
            'type'                                    => 'StillImage',
            'Identification'                          => '',
            'associatedTaxa'                          => '',
            'basisOfRecord'                           => '',
            'catalogNumber'                           => '',
            'collectionCode'                          => '',
            'collectionID'                            => '',
            'coordinateUncertaintyInMeters'           => '',
            'country'                                 => '',
            'county'                                  => '',
            'dateIdentified'                          => '',
            'day'                                     => '',
            'decimalLatitude'                         => '',
            'decimalLongitude'                        => '',
            'eventDate'                               => '',
            'family'                                  => '',
            'genus'                                   => '',
            'geodeticDatum'                           => '',
            'georeferencedBy'                         => '',
            'habitat'                                 => '',
            'identificationQualifier'                 => '',
            'identifiedBy'                            => '',
            'institutionCode'                         => '',
            'kingdom'                                 => '',
            'locality'                                => '',
            'minimumElevationInMeters'                => '',
            'month'                                   => '',
            'municipality'                            => '',
            'occurrenceID'                            => '',
            'occurrenceRemarks'                       => '',
            'order'                                   => '',
            'otherCatalogNumbers'                     => '',
            'phylum'                                  => '',
            'recordNumber'                            => '',
            'recordedBy'                              => '',
            'scientificName'                          => '',
            'scientificNameAuthorship'                => '',
            'specificEpithete0c2'                     => '',
            'startDayOfYear'                          => '',
            'stateProvince'                           => '',
            'taxonIDd400489e5292977038aa17dc95d33d29' => '',
            'verbatimElevation'                       => '',
            'year'                                    => '',
            'PixelXDimension'                         => '',
            'PixelYDimension'                         => '',
            'OriginalFileName'                        => '',
            'associatedRecordReference'               => '',
            'associatedRecordsetReference'            => '',
            'mediaStatus'                             => '',
            'mediaStatusDate'                         => '',
            'recordId'                                => '',
            'Credit'                                  => '',
            'recordEnteredBy'                         => '',
            'CreateDate'                              => '',
            'MetadataDate'                            => '2016-10-04 16:11:21',
            'Owner'                                   => 'Northern Illinois University Herbarium (DEK)',
            'UsageTerms'                              => 'CC BY-NC (Attribution-Non-Commercial)',
            'WebStatement'                            => 'http://creativecommons.org/licenses/by-nc/3.0/',
            'id'                                      => 'f92d8de2-bbd9-4fe0-81f5-e5a7bfe357cf',
            'occurrence'                              => [
                'id' => 'df6cfde3-1093-4352-8c86-c7faa354e7a5',
            ],
        ];
    }
}
