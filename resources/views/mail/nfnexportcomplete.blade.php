@component('mail::message')
# Zooniverse Export Completed

{!! $message !!}

Thank you,<br>
{{ config('app.name') }}
@endcomponent
