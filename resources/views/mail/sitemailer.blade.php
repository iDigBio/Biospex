@component('mail::message')
    {{ t('Hello') }},

    {!! $message !!}

    {{ t('Thank you') }},
    {{ config('app.name') }}
@endcomponent