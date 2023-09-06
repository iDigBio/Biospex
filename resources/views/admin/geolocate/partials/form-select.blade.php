<div class="row">
    <div class="col-sm-10 mx-auto text-center">
        <select id="form_select" class="selectpicker form-select" name="form_select"
                data-url="{{ $route }}"
                data-live-search="true"
                data-actions-box="true"
                title="{{ t('GeoLocate Forms') }}"
                data-header="{{ t('Select New or Saved Form') }}"
                data-width="250"
                data-style="btn-primary">
            <option value="" class="text-uppercase">{{ t('New') }}</option>
            @foreach($expedition->project->group->geoLocateForms as $form)
                <option value="{{ $form->id }}" {{ $expedition->geo_locate_form_id === $form->id ? 'selected' : ''}}>{{ $form->name }}</option>
            @endforeach
        </select>
    </div>
</div>