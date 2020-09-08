@component('mail::message')
    # {{ t('Failed Jobs') }}

    {!! $message !!}

    {{ t('Thank you') }},
    {{ config('app.name') }}
@endcomponent
