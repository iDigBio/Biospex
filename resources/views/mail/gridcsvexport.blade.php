@component('mail::message')
#@lang('emails.grid_export_csv')

{!! $message !!}

Thank you,<br>
{{ config('app.name') }}
@endcomponent
