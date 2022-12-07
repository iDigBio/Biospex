@component('mail::message')
# {{ t('Records Deleted') }}

{!! $message !!}

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
