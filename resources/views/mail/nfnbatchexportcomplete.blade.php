@component('mail::message')
# Zooniverse Batch Export Completed

{!! $message !!}

{!! $links !!}

Thank you,<br>
{{ config('app.name') }}
@endcomponent
