<h3 class="mb-5 mx-auto">{{ t('Select Source') }}:</h3>
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
               required {{ $disableReviewed ? 'disabled' : '' }}>{{ t('Reconciled Expert Review') }}
    </label>
</div>
<div class="col-sm-6 m-auto mt-3 text-justify">{{ t('It is suggested to use the Reconciled Expert Review for Geo Locate. If one does not exist, you can start the procedure in the Expedition tools menu.') }}</div>
