@component('mail::message')
#@lang('pages.import_process_title')

{!! $message !!}

Thank you,<br>
{{ config('app.name') }}
@endcomponent
