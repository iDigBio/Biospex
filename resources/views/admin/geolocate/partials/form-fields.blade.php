<form action="{{ route('admin.geolocates.store', [$expedition->project_id, $expedition->id]) }}" method="post"
      role="form" id="geolocate-form">
    @csrf
    <input type="hidden" id="group_id" name="group_id" value="{{ $form['group_id'] }}">
    <input type="hidden" id="form_data" name="form_data" value="{{ !($form['fields'] === null) }}">
    <input type="hidden" id="entries" name="entries"
           value="{{ old('entries', isset($form['entries'])) ? $form['entries'] : 0 }}">

    <div class="form-group col-sm-10 mx-auto text-center">
        <h3 class="ml-auto mr-auto mb-3">{{ t('Select CSV Source') }}:</h3>
        @if(!$form['expert_file'] && $form['expert_review'])
            <div class="col-sm-8 mb-3 ml-auto mr-auto text-center text-danger">{{ t('Reconciled Expert Review exists but csv file is not published.') }}</div>
        @endif
        @if($form['mismatch_source'])
            <div class="col-sm-8 mb-3 ml-auto mr-auto text-center text-danger">{{ t('The form selected requires Reconciled Expert Review as source but it does not exist for this Expedition. Saving this form will create a new form.') }}</div>
        @endif

        <div class="form-check-inline mb-3">
            <label class="form-check-label">
                <input type="radio"
                       class="form-check-input source"
                       name="source" value="reconcile"
                       data-url="{{ route('admin.geolocates.form', [$expedition->project_id, $expedition->id]) }}"
                       {{ $form['source'] === 'reconcile' ? 'checked' : '' }}
                       required>{{ t('Reconcile Results') }}
            </label>
        </div>
        <div class="form-check-inline mb-3">
            <label class="form-check-label">
                <input type="radio"
                       class="form-check-input source"
                       name="source" value="reconciled"
                       data-url="{{ route('admin.geolocates.form', [$expedition->project_id, $expedition->id]) }}"
                       {{ $form['source'] === 'reconciled' ? 'checked' : '' }}
                       required {{ $form['expert_file'] && $form['expert_review'] ? '' : 'disabled' }}>{{ t('Reconciled Expert Review') }}
            </label>
        </div>
        <div class="col-sm-6 m-auto mt-3 text-justify">{{ t('It is suggested to use the Reconciled Expert Review for GeoLocateExport. If one does not exist, you can start the procedure in the Expedition tools menu.') }}</div>

    </div>
    <div class="form-group col-5 my-3 ml-auto mr-auto">
        <label for="name" class="col-form-label font-bold required">{{ t('Name') }}:</label>
        <input type="text" class="form-control {{ ($errors->has('name')) ? 'is-invalid' : '' }}"
               id="name" name="name"
               value="{{ $form['name'] }}" required>
        <span class="invalid-feedback">{{ $errors->first('name') }}</span>
    </div>
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
    <div class="row">
        <div class="col-sm-10 offset-sm-2 mt-5 text-left">
            <button type="button" class="btn btn-primary pl-4 pr-4 geolocate-btn-add" data-hover="tooltip"
                    title="{{ t('Add New Row') }}"><i
                        class="fas fa-plus"></i></button>
            <button type="button" class="btn btn-primary pl-4 pr-4 geolocate-btn-remove prevent-default"
                    data-hover="tooltip"
                    title="{{ t('Delete Last Row') }}"><i
                        class="fas fa-minus"></i></button>
        </div>
    </div>
    <div class="row mt-3">
        <div id="warning" class="col-sm-10 mx-auto text-center text-danger collapse"></div>
    </div>
    <div class="row mt-3 justify-content-center">
        <button type="submit" class="btn btn-primary pl-4 pr-4 mt-5 text-uppercase m-auto">{{ t('Save') }}</button>
        @isset($form['fields'])
            <button type="button" id="deleteExport" class="btn btn-primary pl-4 pr-4"
                    data-href=" {{ route('admin.geolocates.delete', [$expedition->project_id, $expedition->id]) }}"
                    data-hover="tooltip"
                    data-method="delete"
                    data-confirm="confirmation"
                    title="{{ t('Disassociate Expedition From Form') }}"
                    data-title="{{ t('Disassociate Expedition From Form') }}?"
                    data-content="{{t('This will permanently delete any export files and disassociate the Expedition from the Form. To delete a GeoLocateForm, please visit the Groups section of the site.') }}">
                {{ t('Delete') }}</button>
        @endisset
        @if($form['fields'])
            <button type="button" id="process"
                    data-url="{{ route('admin.geolocates.export', [$expedition->project_id, $expedition->id]) }}"
                    class="btn btn-primary pl-4 pr-4 mt-5 text-uppercase m-auto" {{ $form['exported'] ? 'disabled' : '' }}>{{ t('Export') }}</button>
        @endif
    </div>
</form>
<div class="row default" style="display: none">
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
    <div class="col-sm-6 mt-3">
        <select class="header-select-default" name="fields[999][csv]"
                data-live-search="true"
                data-actions-box="true"
                data-header="{{ t('Select CSV Column') }}"
                data-width="80%"
                data-style="btn-primary">
            <option value="">{{ t('None') }}</option>
            @foreach($form['csv'] as $column)
                <option value="{{ $column }}">{{ $column }}</option>
            @endforeach
        </select>
    </div>
</div>
