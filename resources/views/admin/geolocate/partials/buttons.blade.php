<div class="row mt-3 justify-content-center">
    <button type="submit" class="btn btn-primary pl-4 pr-4 mt-5 text-uppercase m-auto">{{ t('Save') }}</button>
    @isset($form['data'])
        <button type="button" id="process"
                data-url="{{ route('admin.geolocate.export', [$expedition->project_id, $expedition->id]) }}"
                class="btn btn-primary pl-4 pr-4 mt-5 text-uppercase m-auto" {{ $form['exported'] ? 'disabled' : '' }}>{{ t('Export') }}</button>
    @endisset
</div>
