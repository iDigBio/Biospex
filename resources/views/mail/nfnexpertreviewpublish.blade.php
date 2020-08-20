@component('mail::message')
# {{ $title }}

{!! $message !!}

Thank you,<br>
{{ config('app.name') }}
@endcomponent
