@livewire('geolocate-field-manager', [
    'geoOptions' => $form['geo'],
    'csvOptions' => $form['csv'],
    'existingFields' => $form['fields'] ?? [],
    'expedition' => $expedition
])
