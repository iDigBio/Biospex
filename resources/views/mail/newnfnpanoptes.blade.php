@component('mail::message')
## {{ $message }}


{{ $contact }}<br>
{{ $email }}<br>
{{ $title }}<br>
{{ $description }}<br>

Thank you,<br>
{{ config('app.name') }}
@endcomponent
