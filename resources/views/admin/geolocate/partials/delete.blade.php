<div class="row">
    <div class="col-sm-10 mx-auto text-center">
        <button type="button" id="deleteExport" class="btn btn-primary pl-4 pr-4"
                data-href=" {{ route('admin.geolocate.delete', [$expedition->project_id, $expedition->id]) }}"
                data-hover="tooltip"
                data-method="delete"
                data-confirm="confirmation"
                title="{{ t('Delete GeoLocate Form') }}"
                data-title="{{ t('Delete GeoLocate Form') }}?"
                data-content="{{t('This will permanently delete the GeoLocate form and export file.') }}">
            {{ t('Delete Export') }}</button>
    </div>
</div>
