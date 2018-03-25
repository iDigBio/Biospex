@component('mail::message')
#@lang('messages.import_process_title')

{!! $message !!}

Thank you,<br>
{{ config('app.name') }}
@endcomponent
