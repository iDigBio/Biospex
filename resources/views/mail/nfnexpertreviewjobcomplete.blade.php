@component('mail::message')
# {{ $title }}

{!! $message !!}

@component('mail::button', ['url' => $url])
    {{ $button }}
@endcomponent

Thank you,<br>
{{ config('app.name') }}
@endcomponent
