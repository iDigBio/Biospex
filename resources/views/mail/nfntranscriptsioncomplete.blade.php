@component('mail::message')
# Zooniverse Transcriptions Completed

{!! $message !!}

Thank you,<br>
{{ config('app.name') }}
@endcomponent
