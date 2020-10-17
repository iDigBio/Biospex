<h3 class="mb-5 mx-auto">{{ t('Select Export Type') }}:</h3>
<div class="form-check-inline">
    <label class="form-check-label">
        <input type="radio"
               class="form-check-input"
               name="exportType" value="csv"
               {{ isset($data['exportType']) && $data['exportType'] === 'csv' ? 'checked' : '' }}
               required>{{ t('CSV') }}
    </label>
</div>
<div class="form-check-inline disabled">
    <label class="form-check-label">
        <input type="radio"
               class="form-check-input"
               name="exportType" value="dwc"
               {{ isset($data['exportType']) && $data['exportType'] === 'dwc' ? 'checked' : '' }}
               required disabled>{{ t('Darwin Core Archive') }}
    </label>
</div>
