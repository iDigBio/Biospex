@component('mail::message')
# {{ t('NfnPanoptes Transcriptions Completed') }}

{{ t('The NfnPanoptes digitization process for "%s" has been completed.', $title) }}

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
