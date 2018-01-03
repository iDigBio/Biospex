@component('mail::message')
#@lang('errors.import_process_title')

{!! $message !!}
<br>
{{ $file }}

Thank you,<br>
{{ config('app.name') }}
@endcomponent
