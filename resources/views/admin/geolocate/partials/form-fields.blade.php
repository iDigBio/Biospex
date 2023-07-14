<div class="row mt-5">
    <div class="col-sm-6 font-weight-bold">
        {{ t('GeoLocate Fields') }}
    </div>
    <div class="col-sm-6 font-weight-bold">
        {{ t('%s Fields', $sourceType) }}
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
