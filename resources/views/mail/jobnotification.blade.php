@component('mail::message')

{!! $message !!}

@if(!empty($url))
    @component('mail::button', ['url' => $url])
        Join Now
    @endcomponent
@endif

Thank you,<br>
{{ config('app.name') }}
@endcomponent
