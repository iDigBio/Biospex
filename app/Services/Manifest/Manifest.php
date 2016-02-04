<?php

namespace App\Services\Manifest;

use Illuminate\Support\Facades\Config;

class Manifest
{
    private $vars;

    /**
     * Set variables used in class.
     *
     * @param $vars
     */
    public function setVariables($vars)
    {
        $this->vars = $vars;

        return;
    }

    /**
     * Build manifest package variables. $record is expedition.
     *
     * @param $record
     * @param $download
     * @return array
     * @throws \Exception
     */
    public function mapManifestVariables($record, $download)
    {
        if (empty($this->vars)) {
            throw new \Exception(trans('emails.manifest_variables_missing', ['id' => $this->expeditionId]));
        }

        $targetFields = ! empty($record->project->target_fields) ? $this->mapTargetFields($record->project->target_fields) : [];

        $variables = [
            'requestType'             => $this->vars['requestType'],
            'requestSubjectType'      => $this->vars['subjectType'],
            // TODO Placeholder for NfN to tell us what they need.
            'targetFields'            => $targetFields,
            'startDate'               => isset($startDate) ? $startDate : '',
            'endDate'                 => isset($endDate) ? $endDate : '',
            'packageType'             => $this->vars['packageType'],
            'packageIdentifier'       => $record->uuid,
            'packageTitle'            => $record->title,
            'packageDescription'      => $record->description,
            'contacts'                => $this->mapContactVariables($record->project),
            'packageKeywords'         => $record->keywords,
            'packageUpdatedAt'        => "2014-11-18 13:27:20",
            // TODO Expedition updated_at?
            'packageAcknowledgements' => $record->project->project_parnters,
            'geographicScope'         => $record->project->geographic_scope,
            'taxonomicScope'          => $record->project->taxonomic_scope,
            'temporalScope'           => $record->project->temporal_scope,
            'languageSkills'          => $record->project->language_skils,
            'dataSetIdentifier'       => $record->uuid,
            'dataSetUrl'              => Config::get('config.api_url') . '/downloads/' . $download->id,
            'parentType'              => 'digitization project',
            'parentIdentifier'        => $record->project->uuid,
            'parentTitle'             => $record->project->title,
            'parentProvider'          => 'Biospex',
            'parentDescription'       => $record->project->description_short,
            // TODO short
            'parentUrl'               => Config::get('config.app_url') . '/' . $record->project->slug,
            'ppsrFields'              => $record->project->advertise,
            'manifestComment'         => 'Notes from Nature transcription for Biospex Expedition.'
        ];

        return $variables;
    }

    /**
     * Set contact details.
     *
     * @param $project
     * @return array
     */
    private function mapContactVariables($project)
    {
        return [
            'email' => $project->contact_email,
            'name'  => $project->contact,
            'title' => $project->contact_title,
        ];
    }

    /**
     * Build target fields.
     *
     * @param $targetFields
     * @return array|void
     */
    private function mapTargetFields($targetFields = null)
    {
        if (empty($targetFields)) {
            return;
        }

        $fields = [];
        foreach ($targetFields as $field) {
            $fields[] = [
                'name'             => $field['name'],
                'label'            => $field['label'],
                'description'      => $field['description'],
                'validResponse'    => $field['valid_response'],
                'inference'        => $field['inference'],
                'inferenceExample' => $field['inference_example'],
            ];
        }

        return $fields;
    }

}