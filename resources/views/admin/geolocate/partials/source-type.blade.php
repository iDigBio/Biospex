<h3 class="mb-5 mx-auto">{{ t('Select Source') }}:</h3>
<div class="form-check-inline mb-3">
    <label class="form-check-label">
        <input type="radio"
               class="form-check-input"
               name="sourceType" value="review"
               {{ isset($data['sourceType']) && $data['sourceType'] === 'review' ? 'checked' : '' }}
               required {{ !$expedition->nfnActor->pivot->expert ? 'disabled' : '' }}>{{ t('Expert Review') }}
    </label>
</div>
<div class="form-check-inline mb-3">
    <label class="form-check-label">
        <input type="radio"
               class="form-check-input"
               name="sourceType" value="transcription"
               {{ isset($data['sourceType']) && $data['sourceType'] === 'transcription' ? 'checked' : '' }}
               required>{{ t('Transcription Results') }}
    </label>
</div>
<div class="col-sm-6 m-auto mt-3 text-justify">{{ t('It is suggested to use the Expert Review for Geo Locate. If one does not exist, you can start the procedure in the Expedition tools menu.') }}</div>
<!--
if review exists, select it. enable both
if it does not exist, disable it. enable transcription

-->