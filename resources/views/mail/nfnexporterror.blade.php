@component('mail::message')
# Error Exporting For Zooniverse

{!! $message !!}

Thank you,<br>
{{ config('app.name') }}
@endcomponent
