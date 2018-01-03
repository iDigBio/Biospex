@component('mail::message')
# {{ $file }}

{!! $message !!}

Thank you,<br>
{{ config('app.name') }}
@endcomponent
