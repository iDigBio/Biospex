<?php

namespace App\Livewire;

use Livewire\Component;

class GeolocateFieldManager extends Component
{
    // Field entries array
    public $fields = [];

    // Options for dropdowns
    public $geoOptions = [];

    public $csvOptions = [];

    // Expedition data
    public $expedition = null;

    // Number of entries
    public $entries = 0;

    // Validation messages
    public $errorMessage = '';

    protected $rules = [
        'fields.*.geo' => 'required',
        'fields.*.csv' => 'required',
    ];

    protected $messages = [
        'fields.*.geo.required' => 'GeoLocate field is required',
        'fields.*.csv.required' => 'CSV field is required',
    ];

    public function mount($geoOptions = [], $csvOptions = [], $existingFields = [], $expedition = null)
    {
        $this->geoOptions = $geoOptions;
        $this->csvOptions = $csvOptions;
        $this->expedition = $expedition;

        if (! empty($existingFields)) {
            $this->fields = $existingFields;
            $this->entries = count($existingFields);
        } else {
            // Initialize with required fields pre-populated
            $this->initializeRequiredFields();
        }
    }

    /**
     * Initialize the form with required fields pre-populated
     */
    private function initializeRequiredFields()
    {
        $requiredFields = ['County', 'Country', 'Locality', 'ScientificName', 'StateProvince'];

        $this->fields = [];
        foreach ($requiredFields as $field) {
            $this->fields[] = ['geo' => $field, 'csv' => ''];
        }

        $this->entries = count($this->fields);
        $this->errorMessage = '';
    }

    public function addField()
    {
        $this->fields[] = ['geo' => '', 'csv' => ''];
        $this->entries = count($this->fields);
        $this->errorMessage = '';
    }

    public function removeField()
    {
        if (count($this->fields) > 1) {
            array_pop($this->fields);
            $this->entries = count($this->fields);
            $this->errorMessage = '';
        }
    }

    public function resetComponent()
    {
        // Reset to required fields instead of empty field
        $this->initializeRequiredFields();
    }

    protected $listeners = ['resetGeolocateFields' => 'resetComponent'];

    public function checkDuplicates()
    {
        $geoValues = array_column($this->fields, 'geo');
        $geoValues = array_filter($geoValues); // Remove empty values

        return count($geoValues) !== count(array_unique($geoValues));
    }

    public function checkRequiredValues()
    {
        $requiredFields = ['County', 'Country', 'Locality', 'ScientificName', 'StateProvince'];
        $selectedFields = array_column($this->fields, 'geo');
        $selectedFields = array_filter($selectedFields); // Remove empty values

        return array_diff($requiredFields, $selectedFields);
    }

    public function validateFields()
    {
        $this->errorMessage = '';

        if ($this->checkDuplicates()) {
            $this->errorMessage = 'GeoLocate Export fields may not contain duplicate values.';

            return false;
        }

        $missingFields = $this->checkRequiredValues();
        if (! empty($missingFields)) {
            $this->errorMessage = 'GeoLocate requires the fields: '.implode(', ', $missingFields);

            return false;
        }

        return true;
    }

    public function getFieldsData()
    {
        return $this->fields;
    }

    public function render()
    {
        return view('livewire.geolocate-field-manager');
    }
}
