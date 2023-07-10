<h3 class="ml-auto mr-auto mb-3">{{ t('Select Source') }}:</h3>
@if(!$expertFileExists && $expertReviewExists)
    <div class="col-sm-6 mb-3 ml-auto mr-auto text-center text-danger">{{ t('Reconciled Expert Review exists but file is not published.') }}</div>
@endif
<div class="form-check-inline mb-3">
    <label class="form-check-label">
        <input type="radio"
               class="form-check-input sourceType"
               name="sourceType" value="Reconcile Results"
               data-url="{{ route('admin.geolocate.form', [$expedition->project_id, $expedition->id]) }}"
               {{ $sourceType === 'Reconcile Results' ? 'checked' : '' }}
               required>{{ t('Reconcile Results') }}
    </label>
</div>
<div class="form-check-inline mb-3">
    <label class="form-check-label">
        <input type="radio"
               class="form-check-input sourceType"
               name="sourceType" value="Reconciled Expert Review"
               data-url="{{ route('admin.geolocate.form', [$expedition->project_id, $expedition->id]) }}"
               {{ $sourceType === 'Reconciled Expert Review' ? 'checked' : '' }}
               required {{ $expertFileExists && $expertReviewExists ? '' : 'disabled' }}>{{ t('Reconciled Expert Review') }}
    </label>
</div>
<div class="col-sm-6 m-auto mt-3 text-justify">{{ t('It is suggested to use the Reconciled Expert Review for GeoLocate. If one does not exist, you can start the procedure in the Expedition tools menu.') }}</div>
