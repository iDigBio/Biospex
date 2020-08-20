@component('mail::message')
    Hello,

    {!! $message !!}

    Thank you,
    {{ config('app.name') }}
@endcomponent