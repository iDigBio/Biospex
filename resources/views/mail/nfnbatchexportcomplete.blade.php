@component('mail::message')
# {{ t('NfnPanoptes Batch Export Completed') }}

{{ t('The export batches for %s are completed.', $title) }}
{{ t('Click the links below to download batch files. You must be logged into your account on Biospex.') }}

{!! $links !!}

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
