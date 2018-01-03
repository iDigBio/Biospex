@component('mail::message')
# Notes From Nature Transcriptions Completed

{!! $message !!}

Thank you,<br>
{{ config('app.name') }}
@endcomponent
