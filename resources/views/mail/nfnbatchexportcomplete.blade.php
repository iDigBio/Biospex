@component('mail::message')
# Notes From Nature Batch Export Completed

{!! $message !!}

{!! $links !!}

Thank you,<br>
{{ config('app.name') }}
@endcomponent
