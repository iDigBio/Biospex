@component('mail::message')
# {{ t('NfnPanoptes Batch Export Completed') }}

{{ t('The export batches for %s are completed.', $title) }}
{{ t('The links provided below will be valid for 48 hours. Click the links to download each batch file. You must be logged into your account on Biospex.') }}

{!! $links !!}

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
