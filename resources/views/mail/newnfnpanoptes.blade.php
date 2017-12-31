@component('mail::message')
## {{ $message }}


{{ $contact }}<br>
{{ $email }}<br>
{{ $title }}<br>
{{ $description }}<br>

Thanks,<br>
{{ config('app.name') }}
@endcomponent
