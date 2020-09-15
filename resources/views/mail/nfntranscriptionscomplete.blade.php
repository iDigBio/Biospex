@component('mail::message')
# {{ t('Zooniverse Transcriptions Completed') }}

{{ t('The Zooniverse digitization process for "%s" has been completed.', $title) }}

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
