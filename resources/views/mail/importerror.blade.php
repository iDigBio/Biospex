@component('mail::message')
#@lang('errors.import_process_title')

{!! $message !!}
<br>
{{ $file }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
