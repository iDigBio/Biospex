@component('mail::message')
#@lang('messages.import_process_title')

{!! $message !!}
<br>
{{ $file }}

Thank you,<br>
{{ config('app.name') }}
@endcomponent
