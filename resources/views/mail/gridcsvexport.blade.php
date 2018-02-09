@component('mail::message')
#@lang('messages.grid_export_csv')

{!! $message !!}

Thank you,<br>
{{ config('app.name') }}
@endcomponent
