<?php
/**
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services;

use App\Repositories\Interfaces\RapidHeader;
use App\Repositories\Interfaces\RapidRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RapidIngestService
{
    /**
     * @var \App\Services\CsvService
     */
    private $csvService;

    /**
     * @var \App\Repositories\Interfaces\RapidRecord
     */
    private $rapidInterface;

    /**
     * @var \App\Repositories\Interfaces\RapidHeader
     */
    private $headerInterface;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var array
     */
    private $header = [];

    /**
     * @var
     */
    private $rows;

    /**
     * @var array
     */
    private $validationFields;

    /**
     * RapidIngestService constructor.
     *
     * @param \App\Services\CsvService $csvService
     * @param \App\Repositories\Interfaces\RapidRecord $rapidInterface
     * @param \App\Repositories\Interfaces\RapidHeader $headerInterface
     */
    public function __construct(CsvService $csvService, RapidRecord $rapidInterface, RapidHeader $headerInterface)
    {

        $this->csvService = $csvService;
        $this->rapidInterface = $rapidInterface;
        $this->headerInterface = $headerInterface;
        $this->validationFields = config('config.validationFields');
    }

    /**
     * Load csv file.
     *
     * @param $file
     * @throws \League\Csv\Exception
     */
    public function loadCsvFile($file)
    {
        $this->csvService->readerCreateFromPath($file);
        $this->csvService->setDelimiter();
        $this->csvService->setEnclosure();
        $this->csvService->setHeaderOffset();
    }

    /**
     * Set the header.
     *
     * @return array
     */
    public function setHeader(): array
    {
        return $this->header = $this->csvService->getHeader();
    }

    /**
     * Store the header in mongo database.
     */
    public function storeHeader()
    {
        $this->headerInterface->create(['header' => $this->header]);
    }

    /**
     * Set rows.
     */
    public function setRows()
    {
        $this->rows = $this->csvService->getRecords($this->header);
    }

    /**
     * Process rows when importing for first time.
     */
    public function processImportRows()
    {
        foreach ($this->rows as $offset => $row) {
            $this->createRecord($row);
        }
    }

    /**
     * Create rapid record.
     *
     * @param $row
     */
    private function createRecord($row)
    {
        if ($this->validateRow($row)) {
            return;
        }

        $this->rapidInterface->create($row);
    }

    /**
     * Validate imports being created.
     *
     * Fields frrom config.
     * @param $row
     * @return mixed
     */
    public function validateRow($row)
    {
        $attributes = [];
        foreach ($this->validationFields as $field) {
            $attributes[$field] = $row[$field];
        }

        $count = $this->rapidInterface->validateRecord($attributes);

        if ($count) {
            $this->errors[] = $row;
        }

        return $count;
    }

    /**
     * Create csv file.
     *
     * @return string
     * @throws \League\Csv\CannotInsertRecord
     */
    public function createCsv() {
        $errors = collect($this->errors)->recursive();
        $header = $errors->first()->keys();

        $fileName = Str::random() . '.csv';
        $filePath = Storage::path(config('config.reports_dir') . '/' . $fileName);

        $this->csvService->writerCreateFromPath($filePath);
        $this->csvService->insertOne($header);
        $this->csvService->insertAll($errors->toArray());


        return route('admin.download.report', ['fileName' => $fileName]);
    }

    /**
     * Get errors
     *
     * @return bool
     */
    public function checkErrors()
    {
        return ! empty($this->errors);
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

    public function testHeaders()
    {
        return [
            'gbif',
            'idigbio',
            'gbifID_gbifR',
            'idigbio_uuid_idbP',
            'abstract_gbifP',
            'abstract_gbifR',
            'acceptedNameUsage_gbifP',
            'acceptedNameUsage_gbifR',
            'acceptedNameUsage_idbR',
            'acceptedNameUsageID_gbifP',
            'acceptedNameUsageID_gbifR',
            'acceptedNameUsageID_idbR',
            'acceptedScientificName_gbifP',
            'acceptedTaxonKey_gbifP',
            'accessRights_gbifP',
            'accessRights_gbifR',
            'accessRights_idbR',
            'accrualMethod_gbifP',
            'accrualMethod_gbifR',
            'accrualPeriodicity_gbifP',
            'accrualPeriodicity_gbifR',
            'accrualPolicy_gbifP',
            'accrualPolicy_gbifR',
            'aec_associatedTaxa_idbR',
            'alternative_gbifP',
            'alternative_gbifR',
            'associatedMedia_gbifR',
            'associatedMedia_idbR',
            'associatedOccurrences_gbifP',
            'associatedOccurrences_gbifR',
            'associatedOccurrences_idbR',
            'associatedOrganisms_gbifP',
            'associatedOrganisms_gbifR',
            'associatedOrganisms_idbR',
            'associatedReferences_gbifP',
            'associatedReferences_gbifR',
            'associatedReferences_idbR',
            'associatedSequences_gbifP',
            'associatedSequences_gbifR',
            'associatedSequences_idbR',
            'associatedTaxa_gbifP',
            'associatedTaxa_gbifR',
            'associatedTaxa_idbR',
            'audience_gbifP',
            'audience_gbifR',
            'available_gbifP',
            'available_gbifR',
            'basisOfRecord_gbifP',
            'basisOfRecord_gbifR',
            'basisOfRecord_idbP',
            'basisOfRecord_idbR',
            'bed_gbifP',
            'bed_gbifR',
            'bed_idbP',
            'bed_idbR',
            'behavior_gbifP',
            'behavior_gbifR',
            'behavior_idbR',
            'bibliographicCitation_gbifP',
            'bibliographicCitation_gbifR',
            'catalogNumber_gbifP',
            'catalogNumber_gbifR',
            'catalogNumber_idbP',
            'catalogNumber_idbR',
            'class_gbifP',
            'class_gbifR',
            'class_idbP',
            'class_idbR',
            'classKey_gbifP',
            'classs_idbR',
            'collectionCode_gbifP',
            'collectionCode_gbifR',
            'collectionCode_idbP',
            'collectionCode_idbR',
            'collectionID_gbifP',
            'collectionID_gbifR',
            'collectionID_idbP',
            'collectionID_idbR',
            'conformsTo_gbifP',
            'conformsTo_gbifR',
            'continent_gbifP',
            'continent_gbifR',
            'continent_idbP',
            'continent_idbR',
            'contributor_gbifP',
            'contributor_gbifR',
            'coordinatePrecision_gbifP',
            'coordinatePrecision_gbifR',
            'coordinatePrecision_idbR',
            'coordinateUncertaintyInMeters_gbifP',
            'coordinateUncertaintyInMeters_gbifR',
            'coordinateUncertaintyInMeters_idbP',
            'coordinateUncertaintyInMeters_idbR',
            'coreid_idbR',
            'country_gbifR',
            'country_idbP',
            'country_idbR',
            'countryCode_gbifP',
            'countryCode_gbifR',
            'countryCode_idbR',
            'county_gbifP',
            'county_gbifR',
            'county_idbP',
            'county_idbR',
            'coverage_gbifP',
            'coverage_gbifR',
            'created_gbifP',
            'created_gbifR',
            'creator_gbifP',
            'creator_gbifR',
            'dataGeneralizations_gbifP',
            'dataGeneralizations_gbifR',
            'dataGeneralizations_idbR',
            'datasetID_gbifP',
            'datasetID_gbifR',
            'datasetID_idbR',
            'datasetKey_gbifP',
            'datasetName_gbifP',
            'datasetName_gbifR',
            'datasetName_idbR',
            'date_gbifP',
            'date_gbifR',
            'dateAccepted_gbifP',
            'dateAccepted_gbifR',
            'dateCopyrighted_gbifP',
            'dateCopyrighted_gbifR',
            'dateIdentified_gbifP',
            'dateIdentified_gbifR',
            'dateIdentified_idbR',
            'dateSubmitted_gbifP',
            'dateSubmitted_gbifR',
            'day_gbifP',
            'day_gbifR',
            'day_idbR',
            'dc_rights_idbR',
            'dcterms_accessRights_idbR',
            'dcterms_bibliographicCitation_idbR',
            'dcterms_language_idbR',
            'dcterms_license_idbR',
            'dcterms_modified_idbR',
            'dcterms_references_idbR',
            'dcterms_rights_idbR',
            'dcterms_rightsHolder_idbR',
            'dcterms_source_idbR',
            'dcterms_type_idbR',
            'decimalLatitude_gbifP',
            'decimalLatitude_gbifR',
            'decimalLatitude_idbP',
            'decimalLatitude_idbR',
            'decimalLongitude_gbifP',
            'decimalLongitude_gbifR',
            'decimalLongitude_idbP',
            'decimalLongitude_idbR',
            'depth_gbifP',
            'depthAccuracy_gbifP',
            'description_gbifP',
            'description_gbifR',
            'disposition_gbifP',
            'disposition_gbifR',
            'disposition_idbR',
            'distanceAboveSurface_gbifP',
            'distanceAboveSurfaceAccuracy_gbifP',
            'dynamicProperties_gbifP',
            'dynamicProperties_gbifR',
            'dynamicProperties_idbR',
            'earliestAgeOrLowestStage_gbifP',
            'earliestAgeOrLowestStage_gbifR',
            'earliestAgeOrLowestStage_idbP',
            'earliestAgeOrLowestStage_idbR',
            'earliestEonOrLowestEonothem_gbifP',
            'earliestEonOrLowestEonothem_gbifR',
            'earliestEonOrLowestEonothem_idbP',
            'earliestEonOrLowestEonothem_idbR',
            'earliestEpochOrLowestSeries_gbifP',
            'earliestEpochOrLowestSeries_gbifR',
            'earliestEpochOrLowestSeries_idbP',
            'earliestEpochOrLowestSeries_idbR',
            'earliestEraOrLowestErathem_gbifP',
            'earliestEraOrLowestErathem_gbifR',
            'earliestEraOrLowestErathem_idbP',
            'earliestEraOrLowestErathem_idbR',
            'earliestPeriodOrLowestSystem_gbifP',
            'earliestPeriodOrLowestSystem_gbifR',
            'earliestPeriodOrLowestSystem_idbP',
            'earliestPeriodOrLowestSystem_idbR',
            'educationLevel_gbifP',
            'educationLevel_gbifR',
            'elevation_gbifP',
            'elevationAccuracy_gbifP',
            'endDayOfYear_gbifP',
            'endDayOfYear_gbifR',
            'endDayOfYear_idbR',
            'establishmentMeans_gbifP',
            'establishmentMeans_gbifR',
            'establishmentMeans_idbR',
            'eventDate_gbifP',
            'eventDate_gbifR',
            'eventDate_idbP',
            'eventDate_idbR',
            'eventID_gbifP',
            'eventID_gbifR',
            'eventID_idbR',
            'eventRemarks_gbifP',
            'eventRemarks_gbifR',
            'eventRemarks_idbR',
            'eventTime_gbifP',
            'eventTime_gbifR',
            'eventTime_idbR',
            'extent_gbifP',
            'extent_gbifR',
            'family_gbifP',
            'family_gbifR',
            'family_idbP',
            'family_idbR',
            'familyKey_gbifP',
            'fieldNotes_gbifP',
            'fieldNotes_gbifR',
            'fieldNotes_idbR',
            'fieldNumber_gbifP',
            'fieldNumber_gbifR',
            'fieldNumber_idbP',
            'fieldNumber_idbR',
            'footprintSpatialFit_gbifP',
            'footprintSpatialFit_gbifR',
            'footprintSpatialFit_idbR',
            'footprintSRS_gbifP',
            'footprintSRS_gbifR',
            'footprintSRS_idbR',
            'footprintWKT_gbifP',
            'footprintWKT_gbifR',
            'footprintWKT_idbR',
            'format_gbifP',
            'format_gbifR',
            'formation_gbifP',
            'formation_gbifR',
            'formation_idbP',
            'formation_idbR',
            'gbif_canonicalName_idbP',
            'gbif_Identifier_idbR',
            'gbif_Reference_idbR',
            'genericName_gbifP',
            'genus_gbifP',
            'genus_gbifR',
            'genus_idbP',
            'genus_idbR',
            'genusKey_gbifP',
            'geodeticDatum_gbifR',
            'geodeticDatum_idbR',
            'geologicalContextID_gbifP',
            'geologicalContextID_gbifR',
            'geologicalContextID_idbP',
            'geologicalContextID_idbR',
            'georeferencedBy_gbifP',
            'georeferencedBy_gbifR',
            'georeferencedBy_idbR',
            'georeferencedDate_gbifP',
            'georeferencedDate_gbifR',
            'georeferencedDate_idbR',
            'georeferenceProtocol_gbifP',
            'georeferenceProtocol_gbifR',
            'georeferenceProtocol_idbR',
            'georeferenceRemarks_gbifP',
            'georeferenceRemarks_gbifR',
            'georeferenceRemarks_idbR',
            'georeferenceSources_gbifP',
            'georeferenceSources_gbifR',
            'georeferenceSources_idbR',
            'georeferenceVerificationStatus_gbifP',
            'georeferenceVerificationStatus_gbifR',
            'georeferenceVerificationStatus_idbR',
            'group_gbifP',
            'group_gbifR',
            'group_idbP',
            'group_idbR',
            'habitat_gbifP',
            'habitat_gbifR',
            'habitat_idbR',
            'hasCoordinate_gbifP',
            'hasFormat_gbifP',
            'hasFormat_gbifR',
            'hasGeospatialIssues_gbifP',
            'hasPart_gbifP',
            'hasPart_gbifR',
            'hasVersion_gbifP',
            'hasVersion_gbifR',
            'higherClassification_gbifP',
            'higherClassification_gbifR',
            'higherClassification_idbP',
            'higherClassification_idbR',
            'higherGeography_gbifP',
            'higherGeography_gbifR',
            'higherGeography_idbR',
            'higherGeographyID_gbifP',
            'higherGeographyID_gbifR',
            'higherGeographyID_idbR',
            'highestBiostratigraphicZone_gbifP',
            'highestBiostratigraphicZone_gbifR',
            'highestBiostratigraphicZone_idbP',
            'highestBiostratigraphicZone_idbR',
            'id',
            'Identification_idbR',
            'identificationID_gbifP',
            'identificationID_gbifR',
            'identificationID_idbR',
            'identificationQualifier_gbifP',
            'identificationQualifier_gbifR',
            'identificationQualifier_idbR',
            'identificationReferences_gbifP',
            'identificationReferences_gbifR',
            'identificationReferences_idbR',
            'identificationRemarks_gbifP',
            'identificationRemarks_gbifR',
            'identificationRemarks_idbR',
            'identificationVerificationStatus_gbifP',
            'identificationVerificationStatus_gbifR',
            'identificationVerificationStatus_idbR',
            'identifiedBy_gbifP',
            'identifiedBy_gbifR',
            'identifiedBy_idbR',
            'identifiedByID_gbifP',
            'identifiedByID_gbifR',
            'identifier_gbifP',
            'identifier_gbifR',
            'idigbio_associatedsequences_idbP',
            'idigbio_barcodeValue_idbP',
            'idigbio_collectionName_idbP',
            'idigbio_commonnames_idbP',
            'idigbio_dataQualityScore_idbP',
            'idigbio_dateModified_idbP',
            'idigbio_etag_idbP',
            'idigbio_eventDate_idbP',
            'idigbio_flags_idbP',
            'idigbio_hasImage_idbP',
            'idigbio_hasMedia_idbP',
            'idigbio_institutionName_idbP',
            'idigbio_isoCountryCode_idbP',
            'idigbio_mediarecords_idbP',
            'idigbio_recordId_idbR',
            'idigbio_recordIds_idbP',
            'idigbio_recordset_idbP',
            'idigbio_version_idbP',
            'individualCount_gbifP',
            'individualCount_gbifR',
            'individualCount_idbP',
            'individualCount_idbR',
            'informationWithheld_gbifP',
            'informationWithheld_gbifR',
            'informationWithheld_idbR',
            'infraspecificEpithet_gbifP',
            'infraspecificEpithet_gbifR',
            'infraspecificEpithet_idbP',
            'infraspecificEpithet_idbR',
            'institutionCode_gbifP',
            'institutionCode_gbifR',
            'institutionCode_idbP',
            'institutionCode_idbR',
            'institutionID_gbifP',
            'institutionID_gbifR',
            'institutionID_idbP',
            'institutionID_idbR',
            'instructionalMethod_gbifP',
            'instructionalMethod_gbifR',
            'isFormatOf_gbifP',
            'isFormatOf_gbifR',
            'island_gbifP',
            'island_gbifR',
            'island_idbR',
            'islandGroup_gbifP',
            'islandGroup_gbifR',
            'islandGroup_idbR',
            'isPartOf_gbifP',
            'isPartOf_gbifR',
            'isReferencedBy_gbifP',
            'isReferencedBy_gbifR',
            'isReplacedBy_gbifP',
            'isReplacedBy_gbifR',
            'isRequiredBy_gbifP',
            'isRequiredBy_gbifR',
            'issue_gbifP',
            'issued_gbifP',
            'issued_gbifR',
            'isVersionOf_gbifP',
            'isVersionOf_gbifR',
            'kingdom_gbifP',
            'kingdom_gbifR',
            'kingdom_idbP',
            'kingdom_idbR',
            'kingdomKey_gbifP',
            'language_gbifP',
            'language_gbifR',
            'language_idbR',
            'lastCrawled_gbifP',
            'lastInterpreted_gbifP',
            'lastParsed_gbifP',
            'latestAgeOrHighestStage_gbifP',
            'latestAgeOrHighestStage_gbifR',
            'latestAgeOrHighestStage_idbP',
            'latestAgeOrHighestStage_idbR',
            'latestEonOrHighestEonothem_gbifP',
            'latestEonOrHighestEonothem_gbifR',
            'latestEonOrHighestEonothem_idbP',
            'latestEonOrHighestEonothem_idbR',
            'latestEpochOrHighestSeries_gbifP',
            'latestEpochOrHighestSeries_gbifR',
            'latestEpochOrHighestSeries_idbP',
            'latestEpochOrHighestSeries_idbR',
            'latestEraOrHighestErathem_gbifP',
            'latestEraOrHighestErathem_gbifR',
            'latestEraOrHighestErathem_idbP',
            'latestEraOrHighestErathem_idbR',
            'latestPeriodOrHighestSystem_gbifP',
            'latestPeriodOrHighestSystem_gbifR',
            'latestPeriodOrHighestSystem_idbP',
            'latestPeriodOrHighestSystem_idbR',
            'license_gbifP',
            'license_gbifR',
            'lifeStage_gbifP',
            'lifeStage_gbifR',
            'lifeStage_idbR',
            'lithostratigraphicTerms_gbifP',
            'lithostratigraphicTerms_gbifR',
            'lithostratigraphicTerms_idbP',
            'lithostratigraphicTerms_idbR',
            'locality_gbifP',
            'locality_gbifR',
            'locality_idbP',
            'locality_idbR',
            'locationAccordingTo_gbifP',
            'locationAccordingTo_gbifR',
            'locationAccordingTo_idbR',
            'locationID_gbifP',
            'locationID_gbifR',
            'locationID_idbR',
            'locationRemarks_gbifP',
            'locationRemarks_gbifR',
            'locationRemarks_idbR',
            'lowestBiostratigraphicZone_gbifP',
            'lowestBiostratigraphicZone_gbifR',
            'lowestBiostratigraphicZone_idbP',
            'lowestBiostratigraphicZone_idbR',
            'materialSampleID_gbifP',
            'materialSampleID_gbifR',
            'materialSampleID_idbR',
            'maximumDepthInMeters_gbifR',
            'maximumDepthInMeters_idbP',
            'maximumDepthInMeters_idbR',
            'maximumDistanceAboveSurfaceInMeters_gbifP',
            'maximumDistanceAboveSurfaceInMeters_gbifR',
            'maximumElevationInMeters_gbifR',
            'maximumElevationInMeters_idbP',
            'maximumElevationInMeters_idbR',
            'MeasurementOrFact_idbR',
            'mediator_gbifP',
            'mediator_gbifR',
            'mediaType_gbifP',
            'medium_gbifP',
            'medium_gbifR',
            'member_gbifP',
            'member_gbifR',
            'member_idbP',
            'member_idbR',
            'minimumDepthInMeters_gbifR',
            'minimumDepthInMeters_idbP',
            'minimumDepthInMeters_idbR',
            'minimumDistanceAboveSurfaceInMeters_gbifP',
            'minimumDistanceAboveSurfaceInMeters_gbifR',
            'minimumElevationInMeters_gbifR',
            'minimumElevationInMeters_idbP',
            'minimumElevationInMeters_idbR',
            'modified_gbifP',
            'modified_gbifR',
            'modified_idbR',
            'month_gbifP',
            'month_gbifR',
            'month_idbR',
            'municipality_gbifP',
            'municipality_gbifR',
            'municipality_idbP',
            'municipality_idbR',
            'nameAccordingTo_gbifP',
            'nameAccordingTo_gbifR',
            'nameAccordingTo_idbR',
            'nameAccordingToID_gbifP',
            'nameAccordingToID_gbifR',
            'namePublishedIn_gbifP',
            'namePublishedIn_gbifR',
            'namePublishedIn_idbR',
            'namePublishedInID_gbifP',
            'namePublishedInID_gbifR',
            'namePublishedInID_idbR',
            'namePublishedInYear_gbifP',
            'namePublishedInYear_gbifR',
            'namePublishedInYear_idbR',
            'nomenclaturalCode_gbifP',
            'nomenclaturalCode_gbifR',
            'nomenclaturalCode_idbR',
            'nomenclaturalStatus_gbifP',
            'nomenclaturalStatus_gbifR',
            'nomenclaturalStatus_idbR',
            'occurrenceDetails_idbR',
            'occurrenceID_gbifP',
            'occurrenceID_gbifR',
            'occurrenceID_idbP',
            'occurrenceID_idbR',
            'occurrenceRemarks_gbifP',
            'occurrenceRemarks_gbifR',
            'occurrenceRemarks_idbR',
            'occurrenceStatus_gbifP',
            'occurrenceStatus_gbifR',
            'occurrenceStatus_idbR',
            'order_gbifP',
            'order_gbifR',
            'order_idbP',
            'order_idbR',
            'orderKey_gbifP',
            'organismID_gbifP',
            'organismID_gbifR',
            'organismID_idbR',
            'organismName_gbifP',
            'organismName_gbifR',
            'organismName_idbR',
            'organismQuantity_idbR',
            'organismQuantityType_gbifP',
            'organismQuantityType_gbifR',
            'organismQuantityType_idbR',
            'organismRemarks_gbifP',
            'organismRemarks_gbifR',
            'organismRemarks_idbR',
            'organismScope_gbifP',
            'organismScope_gbifR',
            'originalNameUsage_gbifP',
            'originalNameUsage_gbifR',
            'originalNameUsage_idbR',
            'originalNameUsageID_gbifP',
            'originalNameUsageID_gbifR',
            'originalNameUsageID_idbR',
            'otherCatalogNumbers_gbifP',
            'otherCatalogNumbers_gbifR',
            'otherCatalogNumbers_idbR',
            'ownerInstitutionCode_gbifP',
            'ownerInstitutionCode_gbifR',
            'ownerInstitutionCode_idbR',
            'parentEventID_gbifP',
            'parentEventID_gbifR',
            'parentNameUsage_gbifP',
            'parentNameUsage_gbifR',
            'parentNameUsage_idbR',
            'parentNameUsageID_gbifP',
            'parentNameUsageID_gbifR',
            'phylum_gbifP',
            'phylum_gbifR',
            'phylum_idbP',
            'phylum_idbR',
            'phylumKey_gbifP',
            'pointRadiusSpatialFit_gbifP',
            'pointRadiusSpatialFit_gbifR',
            'pointRadiusSpatialFit_idbR',
            'preparations_gbifP',
            'preparations_gbifR',
            'preparations_idbR',
            'previousIdentifications_gbifP',
            'previousIdentifications_gbifR',
            'previousIdentifications_idbR',
            'protocol_gbifP',
            'provenance_gbifP',
            'provenance_gbifR',
            'publisher_gbifP',
            'publisher_gbifR',
            'publisherKey_gbifP',
            'publisherTitle_gbifP',
            'publisherType_gbifP',
            'publishingCountry_gbifP',
            'recordedBy_gbifP',
            'recordedBy_gbifR',
            'recordedBy_idbP',
            'recordedBy_idbR',
            'recordedByID_gbifP',
            'recordedByID_gbifR',
            'recordNumber_gbifP',
            'recordNumber_gbifR',
            'recordNumber_idbP',
            'recordNumber_idbR',
            'references_gbifP',
            'references_gbifR',
            'relation_gbifP',
            'relation_gbifR',
            'relativeOrganismQuantity_gbifP',
            'repatriated_gbifP',
            'replaces_gbifP',
            'replaces_gbifR',
            'reproductiveCondition_gbifP',
            'reproductiveCondition_gbifR',
            'reproductiveCondition_idbR',
            'requires_gbifP',
            'requires_gbifR',
            'ResourceRelationship_idbR',
            'rights_gbifP',
            'rights_gbifR',
            'rights_idbR',
            'rightsHolder_gbifP',
            'rightsHolder_gbifR',
            'rightsHolder_idbR',
            'sampleSizeUnit_gbifP',
            'sampleSizeUnit_gbifR',
            'sampleSizeValue_gbifP',
            'sampleSizeValue_gbifR',
            'sampleSizeValue_idbR',
            'samplingEffort_gbifP',
            'samplingEffort_gbifR',
            'samplingEffort_idbR',
            'samplingProtocol_gbifP',
            'samplingProtocol_gbifR',
            'samplingProtocol_idbR',
            'scientificName_gbifP',
            'scientificName_gbifR',
            'scientificName_idbP',
            'scientificName_idbR',
            'scientificNameAuthorship_gbifR',
            'scientificNameAuthorship_idbR',
            'scientificNameID_gbifP',
            'scientificNameID_gbifR',
            'scientificNameID_idbR',
            'sex_gbifP',
            'sex_gbifR',
            'sex_idbR',
            'source_gbifP',
            'source_gbifR',
            'spatial_gbifP',
            'spatial_gbifR',
            'species_gbifP',
            'speciesKey_gbifP',
            'specificEpithet_gbifP',
            'specificEpithet_gbifR',
            'specificEpithet_idbP',
            'specificEpithet_idbR',
            'startDayOfYear_gbifP',
            'startDayOfYear_gbifR',
            'startDayOfYear_idbP',
            'startDayOfYear_idbR',
            'stateProvince_gbifP',
            'stateProvince_gbifR',
            'stateProvince_idbP',
            'stateProvince_idbR',
            'subgenus_gbifP',
            'subgenus_gbifR',
            'subgenus_idbR',
            'subgenusKey_gbifP',
            'subject_gbifP',
            'subject_gbifR',
            'symbiota_recordEnteredBy_idbR',
            'symbiota_verbatimScientificName_idbR',
            'tableOfContents_gbifP',
            'tableOfContents_gbifR',
            'taxonConceptID_gbifP',
            'taxonConceptID_gbifR',
            'taxonID_gbifP',
            'taxonID_gbifR',
            'taxonID_idbP',
            'taxonID_idbR',
            'taxonKey_gbifP',
            'taxonomicStatus_gbifP',
            'taxonomicStatus_gbifR',
            'taxonomicStatus_idbP',
            'taxonomicStatus_idbR',
            'taxonRank_gbifP',
            'taxonRank_gbifR',
            'taxonRank_idbP',
            'taxonRank_idbR',
            'taxonRemarks_gbifP',
            'taxonRemarks_gbifR',
            'taxonRemarks_idbR',
            'temporal_gbifP',
            'temporal_gbifR',
            'title_gbifP',
            'title_gbifR',
            'type_gbifP',
            'type_gbifR',
            'typeStatus_gbifP',
            'typeStatus_gbifR',
            'typeStatus_idbP',
            'typeStatus_idbR',
            'typifiedName_gbifP',
            'valid_gbifP',
            'valid_gbifR',
            'verbatimCoordinates_gbifR',
            'verbatimCoordinates_idbR',
            'verbatimCoordinateSystem_gbifP',
            'verbatimCoordinateSystem_gbifR',
            'verbatimCoordinateSystem_idbR',
            'verbatimDepth_gbifP',
            'verbatimDepth_gbifR',
            'verbatimDepth_idbR',
            'verbatimElevation_gbifP',
            'verbatimElevation_gbifR',
            'verbatimElevation_idbR',
            'verbatimEventDate_gbifP',
            'verbatimEventDate_gbifR',
            'verbatimEventDate_idbP',
            'verbatimEventDate_idbR',
            'VerbatimEventDate_idbR',
            'verbatimLatitude_gbifR',
            'verbatimLatitude_idbR',
            'verbatimLocality_gbifP',
            'verbatimLocality_gbifR',
            'verbatimLocality_idbP',
            'verbatimLocality_idbR',
            'verbatimLongitude_gbifR',
            'verbatimLongitude_idbR',
            'verbatimScientificName_gbifP',
            'verbatimSRS_gbifP',
            'verbatimSRS_gbifR',
            'verbatimSRS_idbR',
            'verbatimTaxonRank_gbifP',
            'verbatimTaxonRank_gbifR',
            'verbatimTaxonRank_idbR',
            'vernacularName_gbifP',
            'vernacularName_gbifR',
            'vernacularName_idbP',
            'vernacularName_idbR',
            'waterBody_gbifP',
            'waterBody_gbifR',
            'waterBody_idbP',
            'waterBody_idbR',
            'year_gbifP',
            'year_gbifR',
            'year_idbR',
            'zan_ChronometricDate_idbR',
            'country_rapid',
            'countryCode_rapid',
            'test_rapid',
            'another_rapid',
            'gimme_rapid'
        ];
    }
}