@component('mail::message')
# {{ t('The subject import for %s has been completed.', $project) }}

{{ t('OCR processing may take longer and you will receive an email when it is complete.') }}<br>

@isset($dupUrl)
{{ t('Duplicate records found during import can be downloaded using the button below. These records were not entered into the database.') }}
@component('mail::button', ['url' => $dupUrl])
{{ t('Download Duplicates') }}
@endcomponent
@endisset

@isset($rejUrl)
{{ t('Rejected records found during import can be downloaded using the button below. These records were not entered into the database.') }}
@component('mail::button', ['url' => $rejUrl])
{{ t('Download Rejected') }}
@endcomponent
@endisset

{{ t('If clicking button does not work, right click and open in new window.') }}

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
