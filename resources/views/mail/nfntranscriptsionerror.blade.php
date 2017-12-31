@component('mail::message')
# Error Processing Classifications

{!! $message !!}


Thanks,<br>
{{ config('app.name') }}
@endcomponent
