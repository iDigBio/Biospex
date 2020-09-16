@component('mail::message')
# {{ t('Failed Job Errors') }}

{!! $message !!}

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
