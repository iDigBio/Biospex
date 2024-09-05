<div class="row mt-5">
    <div class="col-sm-6 font-weight-bold">
        {{ t('GeoLocateExport Fields') }}
    </div>
    <div class="col-sm-6 font-weight-bold">
        {{ t('CSV Header Fields') }}
    </div>
</div>
<div class="row mt-3">
    <div id="controls" class="controls col-sm-12">
        @for($i=0; $i < $form['entries']; $i++)
            <div class="row entry">
                <div class="col-sm-6 mt-3">
                    <select class="geolocate-field" name="fields[{{$i}}][geo]"
                            data-live-search="true"
                            data-actions-box="true"
                            data-header="{{ t('Select GeoLocateExport Field ( * required)') }}"
                            data-width="80%"
                            data-style="btn-primary"
                            required>
                        <option value="">{{ t('None') }}</option>
                        @foreach($form['geo'] as $key => $value)
                            <option value="{{ is_numeric($key) ? str_replace('*', '', $value) : $key }}"
                                    {{ isset($form['fields']) && $form['fields'][$i]['geo'] === (is_numeric($key) ?
                                        str_replace('*', '', $value) : $key) ? ' selected': '' }}>
                                {{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 mt-3">
                    <select class="header-select" name="fields[{{$i}}][csv]"
                            data-live-search="true"
                            data-header="{{ t('Select CSV Column') }}"
                            data-width="80%"
                            data-style="btn-primary"
                            required>
                        <option value="">{{ t('None') }}</option>
                        @foreach($form['csv'] as $column)
                            <option value="{{ $column }}"{{ isset($form['fields']) && $form['fields'][$i]['csv'] === $column ? ' selected': '' }}>
                                {{ $column }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endfor
    </div>
</div>