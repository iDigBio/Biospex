@component('mail::message')
# Notes From Nature Export Completed

{!! $message !!}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
