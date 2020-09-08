@component('mail::message')
# {{ $file }}

{!! $message !!}

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
