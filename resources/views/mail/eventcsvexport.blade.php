@component('mail::message')
#@lang('messages.event_export_csv')

{!! $message !!}

Thank you,<br>
{{ config('app.name') }}
@endcomponent
