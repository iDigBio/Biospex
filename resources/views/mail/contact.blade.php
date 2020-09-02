@component('mail::message')
    # {{ t('Contact') }}

    {{ t('Name') }}: {{ $contact['name'] }}
    {{ t('Email') }}: {{ $contact['email'] }}
    {{ t('Message') }}: {{ $contact['message'] }}

    {{ t('Thank you') }},
    {{ config('app.name') }}
@endcomponent