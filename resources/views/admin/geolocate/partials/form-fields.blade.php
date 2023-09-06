<form action="{{ route('admin.geolocate.store', [$expedition->project_id, $expedition->id]) }}"
      method="post" role="form" id="geolocate_form">
    @csrf
    <input type="hidden" id="group_id" name="group_id" value="{{ $form['group_id'] }}">
    <input type="hidden" id="form_data" name="form_data" value="{{ !($form['fields'] === null) }}">
    <input type="hidden" id="entries" name="entries"
           value="{{ old('entries', isset($form['entries'])) ? $form['entries'] : 0 }}">

    <div class="form-group col-sm-10 mx-auto text-center">
        @include('admin.geolocate.partials.source')
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
            {{ t('GeoLocate Fields') }}
        </div>
        <div class="col-sm-6 font-weight-bold">
            {{ t('CSV Header Fields') }}
        </div>
    </div>
    <div class="row mt-3">
        <div id="controls" class="col-sm-12">
            @for($i=0; $i < $form['entries']; $i++)
                <div class="row entry">
                    @include('admin.geolocate.partials.geolocate-field-select')
                    @include('admin.geolocate.partials.header-field-select')
                </div>
            @endfor
        </div>
    </div>
    <div class="row">
        <div class="col-sm-10 offset-sm-2 mt-5 text-left">
            <button type="button" class="btn btn-primary pl-4 pr-4 btn-add" data-hover="tooltip"
                    title="{{ t('Add New Row') }}"><i
                        class="fas fa-plus"></i></button>
            <button type="button" class="btn btn-primary pl-4 pr-4 btn-remove prevent-default" data-hover="tooltip"
                    title="{{ t('Delete Last Row') }}"><i
                        class="fas fa-minus"></i></button>
        </div>
    </div>
    <div class="row mt-3">
        <div id="warning" class="col-sm-10 mx-auto text-center text-danger collapse"></div>
    </div>
    @include('admin.geolocate.partials.buttons')
</form>
<div class="row default" style="display: none">
    @include('admin.geolocate.partials.geolocate-field-select-default')
    @include('admin.geolocate.partials.header-field-select-default')
</div>
