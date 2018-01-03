@component('mail::message')
# Error Processing Classifications

{!! $message !!}


Thank you,<br>
{{ config('app.name') }}
@endcomponent
