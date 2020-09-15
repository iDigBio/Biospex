@component('mail::message')
# {{ t('The subject import for %s has been completed.', $project) }}

{{ t('If duplicates or rejects exist, you will find the information in an attached csv file. Duplicates or rejects mean the subjects where not imported into the database.') }}

{{ t('OCR processing may take longer, and you will receive an email when it is complete.') }}<br>

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
