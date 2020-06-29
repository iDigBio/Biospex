@component('mail::message')
#@lang('pages.grid_export_csv')

{!! $message !!}

Thank you,<br>
{{ config('app.name') }}
@endcomponent
