<div class="col-sm-6 mt-3">
    <select class="geolocate-field-default" name="fields[999][geo]"
            data-live-search="true"
            data-actions-box="true"
            data-header="{{ t('Select Geo Locate Field ( * required)') }}"
            data-width="80%"
            data-style="btn-primary">
        <option value="">{{ t('None') }}</option>
        @foreach($form['geo'] as $key => $value)
            <option value="{{ is_numeric($key) ? str_replace('*', '', $value) : $key }}">{{ $value }}</option>
        @endforeach
    </select>
</div>