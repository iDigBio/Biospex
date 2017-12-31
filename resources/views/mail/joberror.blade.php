@component('mail::message')
# {{ $file }}

{!! $message !!}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
