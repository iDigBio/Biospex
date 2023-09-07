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
               data-url="{{ route('admin.geolocate.form', [$expedition->project_id, $expedition->id]) }}"
               {{ $form['source'] === 'reconcile' ? 'checked' : '' }}
               required>{{ t('Reconcile Results') }}
    </label>
</div>
<div class="form-check-inline mb-3">
    <label class="form-check-label">
        <input type="radio"
               class="form-check-input source"
               name="source" value="reconciled"
               data-url="{{ route('admin.geolocate.form', [$expedition->project_id, $expedition->id]) }}"
               {{ $form['source'] === 'reconciled' ? 'checked' : '' }}
               required {{ $form['expert_file'] && $form['expert_review'] ? '' : 'disabled' }}>{{ t('Reconciled Expert Review') }}
    </label>
</div>
<div class="col-sm-6 m-auto mt-3 text-justify">{{ t('It is suggested to use the Reconciled Expert Review for GeoLocate. If one does not exist, you can start the procedure in the Expedition tools menu.') }}</div>
