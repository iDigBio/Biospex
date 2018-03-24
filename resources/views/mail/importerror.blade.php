@component('mail::message')
#@lang('errors.import_process_title')

{!! $message !!}

Thank you,<br>
{{ config('app.name') }}
@endcomponent
