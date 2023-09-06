<div class="row mt-3 justify-content-center">
    <button type="submit" class="btn btn-primary pl-4 pr-4 mt-5 text-uppercase m-auto">{{ t('Save') }}</button>
    @isset($form['fields'])
        <button type="button" id="deleteExport" class="btn btn-primary pl-4 pr-4"
                data-href=" {{ route('admin.geolocate.delete', [$expedition->project_id, $expedition->id]) }}"
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
                data-url="{{ route('admin.geolocate.export', [$expedition->project_id, $expedition->id]) }}"
                class="btn btn-primary pl-4 pr-4 mt-5 text-uppercase m-auto" {{ $form['exported'] ? 'disabled' : '' }}>{{ t('Export') }}</button>
    @endif
</div>
