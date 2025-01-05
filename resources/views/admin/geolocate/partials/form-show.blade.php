<div class="row">
    <div class="controls col-sm-10 mx-auto mt-3 text-center">
        <select id="geolocate-form-select" class="selectpicker" name="geolocate-form-select"
                data-url="{{ route('admin.geolocate-form.show', [$expedition]) }}"
                data-live-search="true"
                data-actions-box="true"
                title="{{ t('New') }}"
                data-header="{{ t('Select New or Saved Form') }}"
                data-width="350"
                data-style="btn-primary">
            <option value="" class="text-uppercase">{{ t('New') }}</option>
            @foreach($expedition->project->group->geoLocateForms as $form)
                <option value="{{ $form->id }}" {{ $expedition->geo_locate_form_id === $form->id ? 'selected' : ''}}>
                    {{ $form->name }}: {{ $form->created_at }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="row">
    <div id="geolocate-form-results" class="col-sm-12 text-center m-auto mt-5">{!! $formFields !!}</div>
</div>