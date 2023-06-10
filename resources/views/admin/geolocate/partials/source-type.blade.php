<h3 class="mb-5 mx-auto">{{ t('Select Source') }}:</h3>
<div class="form-check-inline">
    <label class="form-check-label">
        <input type="radio"
               class="form-check-input"
               name="sourceType" value="review"
               {{ isset($data['sourceType']) && $data['sourceType'] === 'review' ? 'checked' : '' }}
               required>{{ t('Expert Review') }}
    </label>
</div>
<div class="form-check-inline">
    <label class="form-check-label">
        <input type="radio"
               class="form-check-input"
               name="sourceType" value="transcription"
               {{ isset($data['sourceType']) && $data['sourceType'] === 'transcription' ? 'checked' : '' }}
               required>{{ t('Transcription Results') }}
    </label>
</div>
